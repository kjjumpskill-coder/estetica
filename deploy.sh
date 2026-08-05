#!/usr/bin/env bash
#
# Деплой на shared-хостинг ukraine.com.ua через rsync по SSH.
#
#   ./deploy.sh              викласти зміни
#   ./deploy.sh --dry-run    показати, що буде передано, нічого не змінюючи
#
# Реквізити читаються з .env (DEPLOY_HOST, DEPLOY_USER, DEPLOY_PATH, DEPLOY_PORT).
#
# Що НЕ передається і чому:
#   .env                 на сервері свій, з бойовими паролями й токенами
#   storage/originals/   сотні мегабайтів вихідних фото; на сервері потрібні лише
#                        згенеровані WebP, а імпорт ганяється локально
#   public_html/media/   генерується імпортером, свій на кожному боці
#   public_html/cache/   кеш серверний, перезаписувати його ззовні нема сенсу
#   node_modules, .git   тут очевидно
#
# vendor/ передається: composer на shared-хостингу може бути недоступний,
# а залежність усього одна й важить кілька сотень кілобайт.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

if [ ! -f .env ]; then
    echo "Немає .env. Скопіюйте .env.example і заповніть реквізити деплою." >&2
    exit 1
fi

# Читаємо лише потрібні ключі, не засмічуючи оточення рештою.
get() { grep -E "^$1=" .env | head -1 | cut -d= -f2- | tr -d '"' | xargs; }

HOST="$(get DEPLOY_HOST)"
USER="$(get DEPLOY_USER)"
PATH_REMOTE="$(get DEPLOY_PATH)"
PORT="$(get DEPLOY_PORT)"
PORT="${PORT:-22}"

for var in HOST USER PATH_REMOTE; do
    if [ -z "${!var}" ]; then
        echo "У .env не заповнено DEPLOY_${var/PATH_REMOTE/PATH}." >&2
        exit 1
    fi
done

DRY=""
if [ "${1:-}" = "--dry-run" ]; then
    DRY="--dry-run"
    echo "== ПРОБНИЙ ЗАПУСК: нічого не передається =="
fi

if [ ! -d vendor ]; then
    echo "Немає vendor/. Запустіть: docker compose exec app composer install --no-dev -o" >&2
    exit 1
fi

echo "Ціль: ${USER}@${HOST}:${PATH_REMOTE} (порт ${PORT})"

rsync -avz --delete $DRY \
    -e "ssh -p ${PORT}" \
    --exclude '.git/' \
    --exclude '.github/' \
    --exclude 'node_modules/' \
    --exclude '.env' \
    --exclude '.env.example' \
    --exclude 'docker/' \
    --exclude 'docker-compose.yml' \
    --exclude 'docs/' \
    --exclude 'storage/originals/' \
    --exclude 'storage/logs/' \
    --exclude 'public_html/media/' \
    --exclude 'public_html/cache/' \
    --exclude 'shots/' \
    --exclude '.DS_Store' \
    --exclude 'composer.lock' \
    ./ "${USER}@${HOST}:${PATH_REMOTE}/"

if [ -n "$DRY" ]; then
    exit 0
fi

echo
echo "Файли передано. Далі на сервері:"
echo "  1. php bin/migrate.php          застосувати нові міграції"
echo "  2. rm -f public_html/cache/index.html   скинути статичний кеш"
echo
echo "Медіа передається окремо — воно велике й змінюється рідше:"
echo "  rsync -avz -e 'ssh -p ${PORT}' public_html/media/ ${USER}@${HOST}:${PATH_REMOTE}/public_html/media/"
