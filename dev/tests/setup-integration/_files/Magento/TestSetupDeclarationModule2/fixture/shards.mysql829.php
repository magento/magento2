<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'test_table_one' => 'CREATE TABLE `test_table_one` (
  `smallint` smallint NOT NULL AUTO_INCREMENT,
  `varchar` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`smallint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3',
    'test_table_two' => 'CREATE TABLE `test_table_two` (
  `smallint` smallint NOT NULL AUTO_INCREMENT,
  `varchar` varchar(254) DEFAULT NULL,
  PRIMARY KEY (`smallint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3',
    'reference_table' => 'CREATE TABLE `reference_table` (
  `tinyint_ref` tinyint NOT NULL AUTO_INCREMENT,
  `tinyint_without_padding` tinyint NOT NULL DEFAULT \'0\',
  `bigint_without_padding` bigint NOT NULL DEFAULT \'0\',
  `smallint_without_padding` smallint NOT NULL DEFAULT \'0\',
  `integer_without_padding` int NOT NULL DEFAULT \'0\',
  `smallint_with_big_padding` smallint NOT NULL DEFAULT \'0\',
  `smallint_without_default` smallint DEFAULT NULL,
  `int_without_unsigned` int DEFAULT NULL,
  `int_unsigned` int unsigned DEFAULT NULL,
  `bigint_default_nullable` bigint unsigned DEFAULT \'1\',
  `bigint_not_default_not_nullable` bigint unsigned NOT NULL,
  PRIMARY KEY (`tinyint_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3',
    'auto_increment_test' => 'CREATE TABLE `auto_increment_test` (
  `int_auto_increment_with_nullable` int unsigned NOT NULL AUTO_INCREMENT,
  `int_disabled_auto_increment` smallint unsigned DEFAULT \'0\',
  UNIQUE KEY `AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE` (`int_auto_increment_with_nullable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3'
];
