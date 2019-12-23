<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Example\ExampleExtAttribute\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Create table for extension attribute "allow_add_description".
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        $table = $connection->newTable(
            $setup->getTable('extension_attribute_description')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => false, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => false,'unsigned' => false, 'nullable' => false, 'primary' => false],
            'Customer ID'
        )->addColumn(
            'allow_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => false,'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Allow add description'
        )->setComment('Table of customer who is allowed to add a description');

        $connection->createTable($table);
        $setup->endSetup();
    }
}
