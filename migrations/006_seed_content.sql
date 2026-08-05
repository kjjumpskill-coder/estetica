-- Початкове наповнення сайту реальним матеріалом.
--
-- Імпортер свідомо нічого не публікує сам (усе лягає з is_published = 0) — рішення,
-- що показувати, за власницею. Ця міграція робить перший прохід за неї, щоб сайт
-- не стояв порожнім, і будь-що з нього можна вимкнути одним перемикачем в адмінці.
--
-- Прив'язка йде через media.path_base: він містить SHA-1 вмісту файлу, тому
-- ідентифікатори однакові на будь-якій машині, де лежать ті самі оригінали.

-- ---------------------------------------------------------------- Дипломи

UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Канюльне введення полінуклеотидів', d.year = 2024,
       d.issuer = 'TOTIS Pharma', d.sort = 30, d.is_published = 1
 WHERE m.path_base = 'media/diplomas/138b31e34b3f89c2';

-- IMG_6101 — той самий сертифікат, що й попередній, іншим кадром. Лишаємо в базі,
-- але не публікуємо: два однакові дипломи поруч виглядають як помилка.
UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Канюльне введення полінуклеотидів', d.year = 2024,
       d.issuer = 'TOTIS Pharma', d.is_published = 0
 WHERE m.path_base = 'media/diplomas/3e7320cd0fdf636c';

UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Ботулінотерапія', d.year = 2022,
       d.issuer = 'Expert Cosmetology', d.sort = 20, d.is_published = 1
 WHERE m.path_base = 'media/diplomas/1912961b04ec8f5c';

UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Техніка Magic Shading', d.year = 2022,
       d.issuer = 'Perfect Permanent Make-up Academy, Таллінн', d.sort = 40, d.is_published = 1
 WHERE m.path_base = 'media/diplomas/d85c0bd40bb81c77';

UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Техніка Veil on Eyes', d.year = 2020,
       d.issuer = 'Zuieva Anna Permanent Make-up', d.sort = 50, d.is_published = 1
 WHERE m.path_base = 'media/diplomas/f7d00d4cceab738d';

UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Контурна пластика губ', d.year = 2024,
       d.issuer = 'MIRATOX', d.sort = 10, d.is_published = 1
 WHERE m.path_base = 'media/diplomas/0f27587c25a543d6';

UPDATE diplomas d JOIN media m ON m.id = d.media_id
   SET d.title = 'Канюльні техніки', d.year = 2024,
       d.issuer = 'MIRATOX', d.sort = 15, d.is_published = 1
 WHERE m.path_base = 'media/diplomas/4262dd92156e8eb8';

-- ---------------------------------------------------------------- Роботи

-- Alt-текст будується з назви послуги — це і доступність, і SEO.
-- Скрізь «ботулінотерапія»: торгова назва в текстах сайту не вживається,
-- навіть якщо вона вшита в саме зображення.
UPDATE media m
  JOIN works w ON w.after_media_id = m.id
  JOIN services s ON s.id = w.service_id
   SET m.alt = CONCAT(s.title, ' — результат роботи майстра, фото до і після')
 WHERE m.category = 'works';

UPDATE media m
  JOIN works w ON w.after_media_id = m.id
   SET m.alt = 'Робота майстра — фото до і після процедури'
 WHERE m.category = 'works' AND w.service_id IS NULL;

-- Публікуємо все, крім знімків тіла: для них потрібна окрема письмова згода
-- клієнтки, і це рішення власниці, а не розробника.
UPDATE works w
  LEFT JOIN services s ON s.id = w.service_id
   SET w.is_published = 1
 WHERE COALESCE(s.slug, '') <> 'lipolitychni-inyekciyi';

-- ---------------------------------------------------------------- Відгуки

-- Тексти перенабрані українською з реальних листувань. Прізвища скорочені
-- до першої літери; там, де підпис у скріншоті не читається, автор не вказаний —
-- вигадувати ім'я не можна.

INSERT INTO reviews (type, author_name, body, media_id, service_id, review_date, source, sort, is_published)
SELECT 'text', 'Лариса К.',
       'Різниця очевидна. Є відчуття, що звикаю до нового обличчя, але загалом дуже подобається. Очі саме такі, як я хотіла, брови трохи більші, ніж були. Чоловік з порогу сказав: «О, прикольно вийшло!» — значить, справді вийшло.\n\nПройшло трохи більше тижня, я вже подружилася з бровами і стрілками. Багато хто з боку казав, що все виглядає природно й гармонійно. Дякую за роботу і за фотозвіт!',
       m.id, s.id, '2024-06-17', 'Viber', 10, 1
  FROM media m, services s
 WHERE m.path_base = 'media/reviews/a03e2d4cff83e9fb' AND s.slug = 'strilky';

INSERT INTO reviews (type, author_name, body, media_id, service_id, review_date, source, sort, is_published)
SELECT 'text', 'Діана З.',
       'Дівчата, рекомендую від душі цього супер косметолога!\n\nОля, ти супер майстер. Дякую тобі велике.',
       m.id, s.id, '2024-07-27', 'Instagram', 20, 1
  FROM media m, services s
 WHERE m.path_base = 'media/reviews/6cdd7a911f5e1bbf' AND s.slug = 'botulinoterapiya';

INSERT INTO reviews (type, author_name, body, media_id, service_id, review_date, source, sort, is_published)
SELECT 'text', '',
       'Олю, взагалі крутий результат. Супер, дякую!\n\nОля, ти чарівниця.',
       m.id, s.id, '2025-10-22', 'Instagram', 30, 1
  FROM media m, services s
 WHERE m.path_base = 'media/reviews/43eb440402c84991' AND s.slug = 'konturna-plastyka-gub';

INSERT INTO reviews (type, author_name, body, media_id, service_id, review_date, source, sort, is_published)
SELECT 'text', '',
       'Усе забуваю написати — брови бомба. Дякую велике, з першого разу і так вдало.\n\nБажаю вам тільки найкращого і процвітання в кар’єрі. Ви просто класна. Перший раз у житті пишу відгук, і мені приємно.',
       m.id, s.id, '2026-07-08', 'Instagram', 40, 1
  FROM media m, services s
 WHERE m.path_base = 'media/reviews/ba8cc26c179a89cf' AND s.slug = 'brovy';

INSERT INTO reviews (type, author_name, body, media_id, service_id, review_date, source, sort, is_published)
SELECT 'text', '',
       'Олюшко, дякую за шикарні брови! Хочеться змінювати життя, творити, любити, розвиватися. Дякую за позитив і гармонію.\n\nВи нереально класна. Кайфую від себе. До зустрічі!',
       m.id, s.id, '2024-06-28', 'Instagram', 50, 1
  FROM media m, services s
 WHERE m.path_base = 'media/reviews/864e0003a9bd3f33' AND s.slug = 'brovy';

-- ---------------------------------------------------------------- Alt для решти

UPDATE media SET alt = 'Кірілова Ольга — косметолог-естетист, майстер перманентного макіяжу'
 WHERE category = 'master';

UPDATE media SET alt = 'Кабінет салону краси Estetika у Дніпрі'
 WHERE category = 'studio';
