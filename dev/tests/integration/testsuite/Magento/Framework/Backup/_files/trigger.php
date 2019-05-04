<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\Module\Setup;
use Magento\TestFramework\Helper\Bootstrap;

$setup = Bootstrap::getObjectManager()->get(Setup::class);
$tableName = $setup->getTable('test_table_with_custom_trigger');
$table = $setup->getConnection()->newTable(
    $tableName
)->addColumn(
    'id',
    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
    null,
    ['identity' => true, 'nullable' => false, 'primary' => true],
    'ID'
)->setComment(
    'Test table with test custom trigger'
);
$setup->getConnection()->createTable($table);

$trigger = Bootstrap::getObjectManager()->get(TriggerFactory::class)->create()
    ->setName('test_custom_trigger')
    ->setTime(Trigger::TIME_AFTER)
    ->setEvent(Trigger::EVENT_INSERT)
    ->setTable($tableName)
    ->addStatement($setup->getConnection()->quoteInto('SET @test_variable = ?', 'test_value'));
$setup->getConnection()->createTrigger($trigger);
