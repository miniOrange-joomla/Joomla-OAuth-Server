
CREATE TABLE IF NOT EXISTS `#__miniorange_oauthserver_customer` (
`id` int(11) UNSIGNED NOT NULL ,
`email` VARCHAR(255)  NOT NULL ,
`password` VARCHAR(255)  NOT NULL ,
`admin_phone` VARCHAR(255)  NOT NULL ,
`customer_key` VARCHAR(255)  NOT NULL ,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255)  NOT NULL,
`login_status` int(1) DEFAULT 0,
`registration_status` VARCHAR(255) NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11) DEFAULT 0,
`sms_count` int(11) DEFAULT 0,
`uninstall_feedback` int(2) NOT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;



CREATE TABLE IF NOT EXISTS `#__miniorange_oauthserver_config` (
`id` int(10) UNSIGNED AUTO_INCREMENT NOT NULL,
`client_name` varchar(255) NOT NULL ,
`client_secret` varchar(255) NOT NULL, 
`client_id` varchar(255) NOT NULL, 
`authorized_uri` varchar(255) NOT NULL,
`client_count` varchar(255) NOT NULL,
`token_length` int(3) default 64,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

ALTER TABLE `#__users` ADD COLUMN `rancode` varchar(255) DEFAULT 0;
ALTER TABLE `#__users` ADD COLUMN `client_token` varchar(255) DEFAULT 0;
ALTER TABLE `#__users` ADD COLUMN `time_stamp` int(11) DEFAULT 0;

INSERT IGNORE INTO `#__miniorange_oauthserver_config` (`id`,`client_count`) values (1,0);
INSERT IGNORE INTO `#__miniorange_oauthserver_customer`(`id`,`login_status`) values (1,false) ;

