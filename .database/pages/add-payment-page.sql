-- -----------------------------------------------------
-- Adds the Payment landing page used by the frontend `payment` module.
--
-- The Module builds URL rules based on the page slug, so without this row
-- the URLs `/payment/<slug>` (Result) and `/payment/<slug>/Membership/<id>`
-- (Stripe Checkout entry point) will return HTTP 404.
--
-- The script is idempotent: running it multiple times will not create
-- duplicate page or translation rows.
-- -----------------------------------------------------

START TRANSACTION;

-- Create the page only if it does not exist yet
INSERT INTO `page` (`module`, `controller`, `action`, `sort_order`, `default`, `created_at`, `updated_at`, `status`, `deleted`)
SELECT 'payment', 'default', 'index', NULL, 0, NOW(), NOW(), 1, 0
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `page`
    WHERE `module` = 'payment'
      AND `controller` = 'default'
      AND `action` = 'index'
      AND `deleted` = 0
);

-- Look up the page id (works whether the row already existed or was just inserted)
SET @payment_page_id = (
    SELECT `id` FROM `page`
    WHERE `module` = 'payment'
      AND `controller` = 'default'
      AND `action` = 'index'
      AND `deleted` = 0
    ORDER BY `id` ASC
    LIMIT 1
);

-- Add translations for all active languages (PK = (page_id, language_id)).
-- Each slug is translated to keep the URL in the user's language end to end.
INSERT IGNORE INTO `page_translation` (`page_id`, `language_id`, `title`, `slug`, `keywords`, `description`, `content`, `deleted`)
VALUES
    (@payment_page_id, 'en-US', 'Payment',  'checkout',   NULL, NULL, NULL, 0),
    (@payment_page_id, 'ro-RO', 'Plată',    'achitare',   NULL, NULL, NULL, 0),
    (@payment_page_id, 'fr-FR', 'Paiement', 'caisse',     NULL, NULL, NULL, 0),
    (@payment_page_id, 'nl-NL', 'Betaling', 'afrekenen',  NULL, NULL, NULL, 0),
    (@payment_page_id, 'de-DE', 'Zahlung',  'kasse',      NULL, NULL, NULL, 0);

COMMIT;
