-- -----------------------------------------------------
-- Adds the Branches page (route `site/branch`) used by the frontend.
--
-- ApplicationBootstrap builds the URL rules from the page slugs, so without
-- this row `/filiale` (ro), `/en/branches` etc. will return HTTP 404.
--
-- The script is idempotent: running it multiple times will not create
-- duplicate page or translation rows.
-- -----------------------------------------------------

START TRANSACTION;

INSERT INTO `page` (`module`, `controller`, `action`, `sort_order`, `default`, `created_at`, `updated_at`, `status`, `deleted`)
SELECT NULL, 'site', 'branch', NULL, 0, NOW(), NOW(), 1, 0
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `page`
    WHERE `module` IS NULL
      AND `controller` = 'site'
      AND `action` = 'branch'
      AND `deleted` = 0
);

SET @branch_page_id = (
    SELECT `id` FROM `page`
    WHERE `module` IS NULL
      AND `controller` = 'site'
      AND `action` = 'branch'
      AND `deleted` = 0
    ORDER BY `id` ASC
    LIMIT 1
);

INSERT IGNORE INTO `page_translation` (`page_id`, `language_id`, `title`, `slug`, `keywords`, `description`, `content`, `deleted`)
VALUES
    (@branch_page_id, 'en-US', 'Branches',    'branches',    NULL, NULL, NULL, 0),
    (@branch_page_id, 'ro-RO', 'Filiale',     'filiale',     NULL, NULL, NULL, 0),
    (@branch_page_id, 'fr-FR', 'Succursales', 'succursales', NULL, NULL, NULL, 0),
    (@branch_page_id, 'nl-NL', 'Vestigingen', 'vestigingen', NULL, NULL, NULL, 0);

COMMIT;
