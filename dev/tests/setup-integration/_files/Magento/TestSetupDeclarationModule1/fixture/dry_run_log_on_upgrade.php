<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreFile
return ['ALTER TABLE `reference_table` MODIFY COLUMN `tinyint_without_padding` tinyint  NOT NULL   , MODIFY COLUMN `bigint_default_nullable` bigint UNSIGNED NULL DEFAULT 123  , MODIFY COLUMN `bigint_not_default_not_nullable` bigint  NOT NULL   

ALTER TABLE `auto_increment_test` MODIFY COLUMN `int_auto_increment_with_nullable` int UNSIGNED NULL   

ALTER TABLE `test_table` MODIFY COLUMN `float` float(12, 10)  NULL DEFAULT 0 , MODIFY COLUMN `double` double(245, 10)  NULL  , MODIFY COLUMN `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP  , MODIFY COLUMN `varchar` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL  , MODIFY COLUMN `boolean` BOOLEAN NULL DEFAULT 1 

'];
