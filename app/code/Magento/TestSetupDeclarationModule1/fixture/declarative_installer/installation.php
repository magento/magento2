<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'auto_increment_test' => 'CREATE TABLE `auto_increment_test` (
  `int_auto_increment_with_nullable` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `int_disabled_auto_increment` smallint(12) unsigned DEFAULT \'0\',
  UNIQUE KEY `unique_null_key` (`int_auto_increment_with_nullable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'cache' => 'CREATE TABLE `cache` (
  `id` varchar(200) NOT NULL COMMENT \'Cache Id\',
  `data` mediumblob COMMENT \'Cache Data\',
  `create_time` int(11) DEFAULT NULL COMMENT \'Cache Creation Time\',
  `update_time` int(11) DEFAULT NULL COMMENT \'Time of Cache Updating\',
  `expire_time` int(11) DEFAULT NULL COMMENT \'Cache Expiration Time\',
  PRIMARY KEY (`id`),
  KEY `CACHE_EXPIRE_TIME` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Caches\'',
    'cache_tag' => 'CREATE TABLE `cache_tag` (
  `tag` varchar(100) NOT NULL COMMENT \'Tag\',
  `cache_id` varchar(200) NOT NULL COMMENT \'Cache Id\',
  PRIMARY KEY (`tag`,`cache_id`),
  KEY `CACHE_TAG_CACHE_ID` (`cache_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Tag Caches\'',
    'flag' => 'CREATE TABLE `flag` (
  `flag_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT \'Flag Id\',
  `flag_code` varchar(255) NOT NULL COMMENT \'Flag Code\',
  `state` smallint(5) unsigned NOT NULL DEFAULT \'0\' COMMENT \'Flag State\',
  `flag_data` text COMMENT \'Flag Data\',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'Date of Last Flag Update\',
  PRIMARY KEY (`flag_id`),
  KEY `FLAG_LAST_UPDATE` (`last_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Flag\'',
    'reference_table' => 'CREATE TABLE `reference_table` (
  `tinyint_ref` tinyint(7) NOT NULL AUTO_INCREMENT,
  `tinyint_without_padding` tinyint(2) NOT NULL DEFAULT \'0\',
  `bigint_without_padding` bigint(20) NOT NULL DEFAULT \'0\',
  `smallint_without_padding` smallint(5) NOT NULL DEFAULT \'0\',
  `integer_without_padding` int(11) NOT NULL DEFAULT \'0\',
  `smallint_with_big_padding` smallint(254) NOT NULL DEFAULT \'0\',
  `smallint_without_default` smallint(2) DEFAULT NULL,
  `int_without_unsigned` int(2) DEFAULT NULL,
  `int_unsigned` int(2) unsigned DEFAULT NULL,
  `bigint_default_nullable` bigint(2) unsigned DEFAULT \'1\',
  `bigint_not_default_not_nullable` bigint(2) unsigned NOT NULL,
  PRIMARY KEY (`tinyint_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'session' => 'CREATE TABLE `session` (
  `session_id` varchar(255) NOT NULL COMMENT \'Session Id\',
  `session_expires` int(10) unsigned NOT NULL DEFAULT \'0\' COMMENT \'Date of Session Expiration\',
  `session_data` mediumblob NOT NULL COMMENT \'Session Data\',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Database Sessions Storage\'',
    'setup_module' => 'CREATE TABLE `setup_module` (
  `module` varchar(50) NOT NULL COMMENT \'Module\',
  `schema_version` varchar(50) DEFAULT NULL COMMENT \'Schema Version\',
  `data_version` varchar(50) DEFAULT NULL COMMENT \'Data Version\',
  PRIMARY KEY (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'Module versions registry\'',
    'test_table' => 'CREATE TABLE `test_table` (
  `smallint` smallint(3) NOT NULL AUTO_INCREMENT,
  `tinyint` tinyint(7) DEFAULT NULL,
  `bigint` bigint(13) DEFAULT \'0\',
  `float` float(12,4) DEFAULT \'0.0000\',
  `double` decimal(14,6) DEFAULT \'11111111.111111\',
  `decimal` decimal(15,4) DEFAULT \'0.0000\',
  `date` date DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `datetime` datetime DEFAULT \'0000-00-00 00:00:00\',
  `longtext` longtext,
  `mediumtext` mediumtext,
  `varchar` varchar(254) DEFAULT NULL,
  `mediumblob` mediumblob,
  `blob` blob,
  `boolean` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `some_unique_key` (`smallint`,`bigint`),
  KEY `speedup_index` (`tinyint`,`bigint`),
  CONSTRAINT `some_foreign_key` FOREIGN KEY (`tinyint`) REFERENCES `reference_table` (`tinyint_ref`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
];
