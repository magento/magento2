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
];
