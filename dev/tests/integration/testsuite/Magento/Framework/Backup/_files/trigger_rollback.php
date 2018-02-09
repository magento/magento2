<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Module\Setup;
use Magento\TestFramework\Helper\Bootstrap;

$setup = Bootstrap::getObjectManager()->get(Setup::class);
$tableName = $setup->getTable('test_table_with_custom_trigger');
$setup->getConnection()->dropTrigger('test_custom_trigger');
$setup->getConnection()->dropTable($tableName);
