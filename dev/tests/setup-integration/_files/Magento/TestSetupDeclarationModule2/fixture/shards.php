<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'test_table_one' => 'CREATE TABLE `test_table_one` (
  `smallint` smallint(6) NOT NULL AUTO_INCREMENT,
  `varchar` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`smallint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'test_table_two' => 'CREATE TABLE `test_table_two` (
  `smallint` smallint(6) NOT NULL AUTO_INCREMENT,
  `varchar` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`smallint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'reference_table' => 'CREATE TABLE `reference_table` (
  `tinyint_ref` tinyint(4) NOT NULL AUTO_INCREMENT,
  `tinyint_without_padding` tinyint(4) NOT NULL DEFAULT \'0\',
  `bigint_without_padding` bigint(20) NOT NULL DEFAULT \'0\',
  `smallint_without_padding` smallint(6) NOT NULL DEFAULT \'0\',
  `integer_without_padding` int(11) NOT NULL DEFAULT \'0\',
  `smallint_with_big_padding` smallint(6) NOT NULL DEFAULT \'0\',
  `smallint_without_default` smallint(6) DEFAULT NULL,
  `int_without_unsigned` int(11) DEFAULT NULL,
  `int_unsigned` int(10) unsigned DEFAULT NULL,
  `bigint_default_nullable` bigint(20) unsigned DEFAULT \'1\',
  `bigint_not_default_not_nullable` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`tinyint_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'auto_increment_test' => 'CREATE TABLE `auto_increment_test` (
  `int_auto_increment_with_nullable` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `int_disabled_auto_increment` smallint(5) unsigned DEFAULT \'0\',
  UNIQUE KEY `AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE` (`int_auto_increment_with_nullable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8'
];
