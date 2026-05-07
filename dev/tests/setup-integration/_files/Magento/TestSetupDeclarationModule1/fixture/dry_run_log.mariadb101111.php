<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreFile
return ['CREATE TABLE `reference_table` (
`tinyint_ref` tinyint  NOT NULL  AUTO_INCREMENT , 
`tinyint_without_padding` tinyint  NOT NULL DEFAULT 0  , 
`bigint_without_padding` bigint  NOT NULL DEFAULT 0  , 
`smallint_without_padding` smallint  NOT NULL DEFAULT 0  , 
`integer_without_padding` int  NOT NULL DEFAULT 0  , 
`smallint_with_big_padding` smallint  NOT NULL DEFAULT 0  , 
`smallint_without_default` smallint  NULL   , 
`int_without_unsigned` int  NULL   , 
`int_unsigned` int UNSIGNED NULL   , 
`bigint_default_nullable` bigint UNSIGNED NULL DEFAULT 1  , 
`bigint_not_default_not_nullable` bigint UNSIGNED NOT NULL   , 
CONSTRAINT  PRIMARY KEY (`tinyint_ref`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb3 DEFAULT COLLATE=utf8mb3_general_ci 

CREATE TABLE `auto_increment_test` (
`int_auto_increment_with_nullable` int UNSIGNED NOT NULL  AUTO_INCREMENT , 
`int_disabled_auto_increment` smallint UNSIGNED NULL DEFAULT 0  , 
CONSTRAINT `AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE` UNIQUE KEY (`int_auto_increment_with_nullable`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb3 DEFAULT COLLATE=utf8mb3_general_ci 

CREATE TABLE `test_table` (
`smallint` smallint  NOT NULL  AUTO_INCREMENT , 
`tinyint` tinyint  NULL   , 
`bigint` bigint  NULL DEFAULT 0  , 
`float` float(12, 4)  NULL DEFAULT 0 , 
`double` decimal(14, 6)  NULL DEFAULT 11111111.111111 , 
`decimal` decimal(15, 4)  NULL DEFAULT 0 , 
`date` date NULL , 
`timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP , 
`datetime` datetime NULL DEFAULT 0  , 
`longtext` longtext NULL , 
`mediumtext` mediumtext NULL , 
`varchar` varchar(254) NULL  , 
`char` char(255) NULL  , 
`mediumblob` mediumblob NULL , 
`blob` blob NULL , 
`boolean` BOOLEAN NULL  , 
CONSTRAINT `TEST_TABLE_SMALLINT_BIGINT` UNIQUE KEY (`smallint`,`bigint`), 
CONSTRAINT `TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF` FOREIGN KEY (`tinyint`) REFERENCES `reference_table` (`tinyint_ref`)  ON DELETE NO ACTION, 
INDEX `TEST_TABLE_TINYINT_BIGINT` (`tinyint`,`bigint`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb3 DEFAULT COLLATE=utf8mb3_general_ci 

CREATE TABLE `patch_list` (
`patch_id` int  NOT NULL  AUTO_INCREMENT COMMENT "Patch Auto Increment", 
`patch_name` varchar(1024) NOT NULL  COMMENT "Patch Class Name", 
CONSTRAINT  PRIMARY KEY (`patch_id`)
) ENGINE=innodb DEFAULT CHARSET=utf8mb3 DEFAULT COLLATE=utf8mb3_general_ci COMMENT="List of data/schema patches"

'];
