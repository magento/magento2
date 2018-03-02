<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
return ['CREATE TABLE `reference_table` (
`tinyint_ref` tinyint(7)  NOT NULL  AUTO_INCREMENT , 
`tinyint_without_padding` tinyint(2)  NOT NULL DEFAULT 0  , 
`bigint_without_padding` bigint(20)  NOT NULL DEFAULT 0  , 
`smallint_without_padding` smallint(5)  NOT NULL DEFAULT 0  , 
`integer_without_padding` int(11)  NOT NULL DEFAULT 0  , 
`smallint_with_big_padding` smallint(254)  NOT NULL DEFAULT 0  , 
`smallint_without_default` smallint(2)  NULL   , 
`int_without_unsigned` int(2)  NULL   , 
`int_unsigned` int(2) UNSIGNED NULL   , 
`bigint_default_nullable` bigint(2) UNSIGNED NULL DEFAULT 1  , 
`bigint_not_default_not_nullable` bigint(2) UNSIGNED NOT NULL   , 
CONSTRAINT  PRIMARY KEY (`tinyint_ref`)
) ENGINE=innodb 

CREATE TABLE `auto_increment_test` (
`int_auto_increment_with_nullable` int(12) UNSIGNED NOT NULL  AUTO_INCREMENT , 
`int_disabled_auto_increment` smallint(12) UNSIGNED NULL DEFAULT 0  , 
CONSTRAINT `unique_null_key` UNIQUE KEY (`int_auto_increment_with_nullable`)
) ENGINE=innodb 

CREATE TABLE `test_table` (
`smallint` smallint(3)  NOT NULL  AUTO_INCREMENT , 
`tinyint` tinyint(7)  NULL   , 
`bigint` bigint(13)  NULL DEFAULT 0  , 
`float` float(12, 4)  NULL DEFAULT 0 , 
`double` decimal(14, 6)  NULL DEFAULT 11111111.111111 , 
`decimal` decimal(15, 4)  NULL DEFAULT 0 , 
`date` date NULL , 
`timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , 
`datetime` datetime NULL DEFAULT 0  , 
`longtext` longtext NULL , 
`mediumtext` mediumtext NULL , 
`varchar` varchar(254) NULL  , 
`mediumblob` mediumblob NULL , 
`blob` blob NULL , 
`boolean` BOOLEAN NULL  , 
CONSTRAINT `some_unique_key` UNIQUE KEY (`smallint`,`bigint`), 
CONSTRAINT `some_foreign_key` FOREIGN KEY (`tinyint`) REFERENCES `reference_table` (`tinyint_ref`)  ON DELETE NO ACTION, 
INDEX `speedup_index` (`tinyint`,`bigint`)
) ENGINE=innodb 

CREATE TABLE `patch_list` (
`patch_id` int(11)  NOT NULL  AUTO_INCREMENT COMMENT "Patch Auto Increment", 
`patch_name` varchar(1024) NOT NULL  COMMENT "Patch Class Name", 
CONSTRAINT  PRIMARY KEY (`patch_id`)
) ENGINE=innodb COMMENT="List of data/schema patches"

'];
