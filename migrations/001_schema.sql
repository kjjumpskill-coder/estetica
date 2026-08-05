-- Базова схема. Порядок таблиць враховує зовнішні ключі: media і services створюються
-- першими, бо на них посилаються майже всі інші.

CREATE TABLE media (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category    ENUM('works','reviews','diplomas','studio','master','blog') NOT NULL,
    path_base   VARCHAR(255) NOT NULL COMMENT 'шлях без суфікса розміру: media/works/abc123',
    width       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    height      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    lqip        TEXT NULL COMMENT 'base64 WebP 20px для плавної підміни',
    sha1        CHAR(40) NOT NULL COMMENT 'дедуп при повторному імпорті',
    alt         VARCHAR(255) NOT NULL DEFAULT '',
    -- Частина фото знята догори ногами, і це не EXIF-орієнтація: камера справді так
    -- знімала. Автоповорот їх не виправить, потрібен ручний кут із адмінки.
    orientation SMALLINT NOT NULL DEFAULT 0 COMMENT 'кут повороту: 0, 90, 180, 270',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_media_sha1 (sha1),
    KEY idx_media_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_slug    ENUM('permanent','injection') NOT NULL,
    slug          VARCHAR(80) NOT NULL,
    title         VARCHAR(160) NOT NULL,
    duration_text VARCHAR(80) NOT NULL DEFAULT '',
    short_desc    VARCHAR(400) NOT NULL DEFAULT '',
    full_desc     TEXT NULL,
    icon          VARCHAR(40) NOT NULL DEFAULT '',
    sort          SMALLINT NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_services_slug (slug),
    KEY idx_services_group (group_slug, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE works (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id     INT UNSIGNED NULL,
    -- Ключове поле. Наявний матеріал — готові інстаграмні колажі, де «до» і «після»
    -- вже склеєні в одну картинку, тому слайдер-порівняння на них неможливий.
    --   collage — одне зображення, галерея з лайтбоксом
    --   single  — фото результату без «до»
    --   pair    — дві окремі картинки, вмикається слайдер
    -- Слайдер написаний одразу, але активується лише для pair. Коли з'являться
    -- роздільні знімки, блок починає працювати без переробки.
    layout         ENUM('collage','single','pair') NOT NULL DEFAULT 'collage',
    before_media_id INT UNSIGNED NULL,
    after_media_id  INT UNSIGNED NOT NULL COMMENT 'для collage і single — саме тут лежить зображення',
    caption        VARCHAR(255) NOT NULL DEFAULT '',
    sort           SMALLINT NOT NULL DEFAULT 0,
    is_published   TINYINT(1) NOT NULL DEFAULT 0,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_works_service_published (service_id, is_published),
    KEY idx_works_sort (sort),
    CONSTRAINT fk_works_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL,
    CONSTRAINT fk_works_before  FOREIGN KEY (before_media_id) REFERENCES media (id) ON DELETE SET NULL,
    CONSTRAINT fk_works_after   FOREIGN KEY (after_media_id)  REFERENCES media (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type        ENUM('text','screenshot','video') NOT NULL DEFAULT 'text',
    author_name VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'ім''я + перша літера прізвища',
    -- Основне поле. Вихідні відгуки — скріншоти російськомовних листувань із повними
    -- іменами клієнток. Перенабрані українською вони і не порушують приватності,
    -- і дають індексований текст для schema.org/Review, чого скріншот не дає.
    body        TEXT NULL,
    media_id    INT UNSIGNED NULL COMMENT 'опційний скрін — показується по кліку',
    video_url   VARCHAR(255) NULL,
    service_id  INT UNSIGNED NULL,
    review_date DATE NULL,
    source      VARCHAR(60) NOT NULL DEFAULT '',
    sort        SMALLINT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_reviews_published (is_published, sort),
    CONSTRAINT fk_reviews_media   FOREIGN KEY (media_id)   REFERENCES media (id) ON DELETE SET NULL,
    CONSTRAINT fk_reviews_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE diplomas (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL DEFAULT '',
    year         SMALLINT UNSIGNED NULL,
    issuer       VARCHAR(160) NOT NULL DEFAULT '',
    media_id     INT UNSIGNED NOT NULL,
    is_award     TINYINT(1) NOT NULL DEFAULT 0,
    sort         SMALLINT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    KEY idx_diplomas_published (is_published, sort),
    CONSTRAINT fk_diplomas_media FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE faq (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tab          ENUM('faq','contra','prep','aftercare') NOT NULL,
    question     VARCHAR(255) NOT NULL,
    answer       TEXT NOT NULL,
    sort         SMALLINT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_faq_tab (tab, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE posts (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug           VARCHAR(120) NOT NULL,
    title          VARCHAR(255) NOT NULL,
    excerpt        VARCHAR(400) NOT NULL DEFAULT '',
    body           MEDIUMTEXT NULL COMMENT 'markdown',
    cover_media_id INT UNSIGNED NULL,
    published_at   DATETIME NULL,
    is_published   TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_posts_slug (slug),
    KEY idx_posts_published (is_published, published_at),
    CONSTRAINT fk_posts_cover FOREIGN KEY (cover_media_id) REFERENCES media (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120) NOT NULL,
    phone           VARCHAR(32) NOT NULL,
    contact_channel ENUM('phone','telegram','viber','whatsapp') NOT NULL DEFAULT 'phone',
    service_id      INT UNSIGNED NULL,
    preferred_date  VARCHAR(120) NOT NULL DEFAULT '' COMMENT 'побажання вільним текстом, не бронювання',
    comment         TEXT NULL,
    page_path       VARCHAR(255) NOT NULL DEFAULT '',
    utm_source      VARCHAR(120) NOT NULL DEFAULT '',
    utm_medium      VARCHAR(120) NOT NULL DEFAULT '',
    utm_campaign    VARCHAR(120) NOT NULL DEFAULT '',
    referrer        VARCHAR(255) NOT NULL DEFAULT '',
    source          ENUM('site','bot') NOT NULL DEFAULT 'site',
    status          ENUM('new','in_work','booked','no_answer','declined') NOT NULL DEFAULT 'new',
    admin_note      TEXT NULL,
    ip_hash         CHAR(64) NOT NULL DEFAULT '' COMMENT 'для rate limit, без зберігання самої IP',
    tg_message_id   BIGINT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_leads_created (created_at),
    KEY idx_leads_status (status, created_at),
    KEY idx_leads_ip (ip_hash, created_at),
    CONSTRAINT fk_leads_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    `key`   VARCHAR(80) NOT NULL PRIMARY KEY,
    `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    login           VARCHAR(60) NOT NULL,
    pass_hash       VARCHAR(255) NOT NULL,
    totp_secret     VARCHAR(64) NULL,
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until    DATETIME NULL,
    last_login_at   DATETIME NULL,
    UNIQUE KEY uniq_admin_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bot_subscribers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id       BIGINT NOT NULL,
    first_name    VARCHAR(120) NOT NULL DEFAULT '',
    username      VARCHAR(120) NOT NULL DEFAULT '',
    source        VARCHAR(60) NOT NULL DEFAULT '',
    is_owner      TINYINT(1) NOT NULL DEFAULT 0,
    subscribed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_bot_chat (chat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE page_views (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    path         VARCHAR(255) NOT NULL DEFAULT '/',
    visitor_hash CHAR(64) NOT NULL,
    referrer     VARCHAR(255) NOT NULL DEFAULT '',
    utm_source   VARCHAR(120) NOT NULL DEFAULT '',
    device       ENUM('mobile','tablet','desktop') NOT NULL DEFAULT 'desktop',
    is_new       TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_views_created (created_at),
    KEY idx_views_visitor (visitor_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type         VARCHAR(60) NOT NULL,
    label        VARCHAR(160) NOT NULL DEFAULT '',
    visitor_hash CHAR(64) NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_events_type_created (type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stats_daily (
    `date`     DATE NOT NULL PRIMARY KEY,
    views      INT UNSIGNED NOT NULL DEFAULT 0,
    visitors   INT UNSIGNED NOT NULL DEFAULT 0,
    leads      INT UNSIGNED NOT NULL DEFAULT 0,
    top_source VARCHAR(120) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
