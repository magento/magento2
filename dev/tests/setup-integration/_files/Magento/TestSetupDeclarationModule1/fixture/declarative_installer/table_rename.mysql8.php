<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

return [
    'before' => 'CREATE TABLE `some_table` (
  `some_column` varchar(255) DEFAULT NULL COMMENT \'Some Column Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3',
    'after' => 'CREATE TABLE `some_table_renamed` (
  `some_column` varchar(255) DEFAULT NULL COMMENT \'Some Column Name\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3',
];
