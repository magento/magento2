<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'before' => 'CREATE TABLE `some_table` (
  `some_column` varchar(255) DEFAULT NULL COMMENT \'Some Column Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
    'after' => 'CREATE TABLE `some_table_renamed` (
  `some_column` varchar(255) DEFAULT NULL COMMENT \'Some Column Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
];
