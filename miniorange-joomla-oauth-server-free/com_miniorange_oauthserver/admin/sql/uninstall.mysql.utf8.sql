DROP TABLE IF EXISTS `#__miniorange_oauthserver_config`;
DROP TABLE IF EXISTS `#__miniorange_oauthserver_customer`;
ALTER TABLE `#__users` DROP COLUMN `rancode`;
ALTER TABLE `#__users` DROP COLUMN `client_token`;
ALTER TABLE `#__users` DROP COLUMN`time_stamp`;