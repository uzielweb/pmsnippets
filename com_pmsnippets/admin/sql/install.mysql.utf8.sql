SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `#__pmsnippets_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `catid` INT UNSIGNED NOT NULL DEFAULT 0,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `alias` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `summary` VARCHAR(500) NOT NULL DEFAULT '',
    `description` MEDIUMTEXT,
    `code_language` VARCHAR(32) NOT NULL DEFAULT 'php',
    `context_type` VARCHAR(32) NOT NULL DEFAULT 'override',
    `difficulty` VARCHAR(20) NOT NULL DEFAULT 'intermediate',
    `code_blocks` MEDIUMTEXT,
    `code_search_text` MEDIUMTEXT,
    `source_url` VARCHAR(500) NOT NULL DEFAULT '',
    `featured` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `state` TINYINT NOT NULL DEFAULT 1,
    `access` INT UNSIGNED NOT NULL DEFAULT 1,
    `ordering` INT NOT NULL DEFAULT 0,
    `hits` INT UNSIGNED NOT NULL DEFAULT 0,
    `copies_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `useful_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `language` CHAR(7) NOT NULL DEFAULT '*',
    `metadesc` VARCHAR(1024) NOT NULL DEFAULT '',
    `metakey` VARCHAR(1024) NOT NULL DEFAULT '',
    `metadata` TEXT,
    `created` DATETIME NOT NULL,
    `created_by` INT UNSIGNED NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `modified_by` INT UNSIGNED NOT NULL DEFAULT 0,
    `publish_up` DATETIME NULL DEFAULT NULL,
    `publish_down` DATETIME NULL DEFAULT NULL,
    `checked_out` INT UNSIGNED NULL DEFAULT NULL,
    `checked_out_time` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_state_access_lang` (`state`, `access`, `language`),
    KEY `idx_catid` (`catid`),
    KEY `idx_alias` (`alias`(191)),
    KEY `idx_context_lang` (`context_type`, `code_language`),
    KEY `idx_featured_copies` (`featured`, `copies_count`, `hits`),
    FULLTEXT KEY `ft_pmsnippets_search` (`title`, `summary`, `description`, `code_search_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__pmsnippets_item_versions` (
    `snippet_id` INT UNSIGNED NOT NULL,
    `version_code` VARCHAR(16) NOT NULL,
    PRIMARY KEY (`snippet_id`, `version_code`),
    KEY `idx_version_code` (`version_code`),
    CONSTRAINT `fk_pmsnippets_item_versions_item`
        FOREIGN KEY (`snippet_id`) REFERENCES `#__pmsnippets_items` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
