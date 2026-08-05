#!/usr/bin/env bash
#
# HEIC → JPG перед імпортом.
#
# Айфон знімає в HEIC, а PHP GD на shared-хостингу цей формат не відкриває взагалі.
# Тому конвертація робиться тут, на macOS, вбудованим sips — і в storage/originals
# потрапляють уже придатні файли.
#
#   ./bin/heic-convert.sh                     всі HEIC у storage/originals
#   ./bin/heic-convert.sh path/to/folder      конкретна папка
#
# Оригінальні .heic не видаляються — вони переносяться в сусідню теку _heic,
# щоб імпортер їх більше не бачив, а ви за потреби могли повернутись до джерела.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TARGET="${1:-$ROOT/storage/originals}"

if ! command -v sips >/dev/null 2>&1; then
    echo "sips не знайдено. Скрипт розрахований на macOS." >&2
    exit 1
fi

if [ ! -d "$TARGET" ]; then
    echo "Немає теки: $TARGET" >&2
    exit 1
fi

converted=0
skipped=0

while IFS= read -r -d '' file; do
    dir="$(dirname "$file")"
    base="$(basename "${file%.*}")"
    out="$dir/$base.jpg"

    if [ -f "$out" ]; then
        skipped=$((skipped + 1))
        continue
    fi

    if sips -s format jpeg -s formatOptions 92 "$file" --out "$out" >/dev/null 2>&1; then
        mkdir -p "$dir/_heic"
        mv "$file" "$dir/_heic/"
        converted=$((converted + 1))
        echo "  ок: $base.heic → $base.jpg"
    else
        echo "  ПОМИЛКА конвертації: $file" >&2
    fi
done < <(find "$TARGET" -type f \( -iname '*.heic' -o -iname '*.heif' \) -not -path '*/_heic/*' -print0)

echo
echo "Сконвертовано: $converted, пропущено (вже є jpg): $skipped"
