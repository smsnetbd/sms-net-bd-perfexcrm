<?php

defined('BASEPATH') || exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix().ALPHASMS_MODULE_NAME.'_sms')) {
    $CI->db->query('CREATE TABLE `'.db_prefix().ALPHASMS_MODULE_NAME.'_sms` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `hash` VARCHAR(32) NOT NULL,
        `testsms` TINYINT(1) NOT NULL DEFAULT 0,
        `sms_from` VARCHAR(100) DEFAULT NULL,
        `sms_to` VARCHAR(20) DEFAULT NULL,
        `sms_message` VARCHAR(255) DEFAULT NULL,
        `error` INT DEFAULT NULL,
        `error_message` VARCHAR(255) DEFAULT NULL,
        `request_id` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `hash` (`hash`),
        UNIQUE KEY `request_id` (`request_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
}

