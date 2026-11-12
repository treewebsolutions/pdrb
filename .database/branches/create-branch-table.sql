-- -----------------------------------------------------
-- Branches (Filiale): table, RBAC permissions, translations.
--
-- - Creates the `branch` table (contact-like data per branch + sort order).
--   `schedule` holds the multilingual schedule as JSON: {"ro-RO": "...", "en-US": "..."}.
-- - Inserts the view/create/update/delete/restore Branch permissions
--   (type = 2 = Item::TYPE_PERMISSION) and attaches them to `superAdmin`.
-- - Adds the "Branch"/"Branches" menu labels translations.
--
-- The script is idempotent: running it multiple times will not create duplicates.
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `branch` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb3_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `middle_name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `registration_number` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `tin` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `mobile_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fixed_phone` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `schedule` text COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `street_name` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `street_number` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `locality` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `zip_code` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `county` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `latitude` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` tinyint NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `deleted` (`deleted`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

START TRANSACTION;

INSERT IGNORE INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('viewBranch', 2, NULL, NULL, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('createBranch', 2, NULL, NULL, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('updateBranch', 2, NULL, NULL, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('deleteBranch', 2, NULL, NULL, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('restoreBranch', 2, NULL, NULL, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT IGNORE INTO `auth_item_child` (`parent`, `child`)
SELECT 'superAdmin', p.`name`
FROM `auth_item` p
WHERE p.`name` IN ('viewBranch', 'createBranch', 'updateBranch', 'deleteBranch', 'restoreBranch')
  AND EXISTS (SELECT 1 FROM `auth_item` r WHERE r.`name` = 'superAdmin' AND r.`type` = 1);

-- Translations (category `common`)
INSERT INTO `language_source` (`category`, `message`)
SELECT 'common', m.`message`
FROM (SELECT 'Branch' AS `message` UNION ALL SELECT 'Branches') m
WHERE NOT EXISTS (SELECT 1 FROM `language_source` s WHERE s.`category` = 'common' AND s.`message` = m.`message`);

INSERT IGNORE INTO `language_translate` (`id`, `language`, `translation`)
SELECT s.`id`, t.`language`, t.`translation`
FROM `language_source` s
JOIN (
  SELECT 'Branch' AS `message`, 'ro-RO' AS `language`, 'Filială' AS `translation`
  UNION ALL SELECT 'Branches', 'ro-RO', 'Filiale'
  UNION ALL SELECT 'Branch', 'en-US', 'Branch'
  UNION ALL SELECT 'Branches', 'en-US', 'Branches'
  UNION ALL SELECT 'Branch', 'fr-FR', 'Succursale'
  UNION ALL SELECT 'Branches', 'fr-FR', 'Succursales'
  UNION ALL SELECT 'Branch', 'nl-NL', 'Vestiging'
  UNION ALL SELECT 'Branches', 'nl-NL', 'Vestigingen'
) t ON t.`message` = s.`message`
WHERE s.`category` = 'common';

COMMIT;

-- Contact page link text (category `frontend`)
START TRANSACTION;

INSERT INTO `language_source` (`category`, `message`)
SELECT 'frontend', 'See all branches'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `language_source` s WHERE s.`category` = 'frontend' AND s.`message` = 'See all branches');

INSERT IGNORE INTO `language_translate` (`id`, `language`, `translation`)
SELECT s.`id`, t.`language`, t.`translation`
FROM `language_source` s
JOIN (
  SELECT 'ro-RO' AS `language`, 'Vezi toate filialele' AS `translation`
  UNION ALL SELECT 'en-US', 'See all branches'
  UNION ALL SELECT 'fr-FR', 'Voir toutes les succursales'
  UNION ALL SELECT 'nl-NL', 'Bekijk alle vestigingen'
) t
WHERE s.`category` = 'frontend' AND s.`message` = 'See all branches';

COMMIT;
