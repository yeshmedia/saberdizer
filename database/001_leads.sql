CREATE TABLE IF NOT EXISTS course_leads (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    whatsapp VARCHAR(13) NOT NULL,
    email VARCHAR(190) NULL,
    source VARCHAR(32) NOT NULL DEFAULT 'landing_page',
    consent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_course_leads_whatsapp (whatsapp),
    KEY idx_course_leads_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
