-- -----------------------------------------------------
-- Adds the `updatePaymentSetting` permission to the RBAC system.
--
-- - Inserts the permission into `auth_item` (type = 2 = Item::TYPE_PERMISSION).
-- - Assigns the permission to the `superAdmin` role (if it exists).
--
-- The script is idempotent: running it multiple times will not create duplicates.
-- -----------------------------------------------------

START TRANSACTION;

-- Insert the permission (type=2 means Yii\rbac\Item::TYPE_PERMISSION)
INSERT IGNORE INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`)
VALUES ('updatePaymentSetting', 2, NULL, NULL, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Attach the permission to the `superAdmin` role (only if the role exists)
INSERT IGNORE INTO `auth_item_child` (`parent`, `child`)
SELECT 'superAdmin', 'updatePaymentSetting'
FROM `auth_item`
WHERE `name` = 'superAdmin' AND `type` = 1;

COMMIT;
