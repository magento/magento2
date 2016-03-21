<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema adds new table `eav_attribute_option_swatch`
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $contextInterface)
    {
        $setup->startSetup();

        $swatchTable = $setup->getTable('eav_attribute_option_swatch');

        /** Creating the main_table 'eav_attribute_option_swatch' */
        $table = $setup->getConnection()
            ->newTable($swatchTable)
            ->addColumn(
                'swatch_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Swatch ID'
            )
            ->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
                'Option ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
                'Store ID'
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
                'Swatch type: 0 - text, 1 - visual color, 2 - visual image'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'unsigned' => false, 'nullable' => true, 'primary' => false],
                'Swatch Value'
            )
            ->addIndex(
                $setup->getIdxName($swatchTable, ['swatch_id']),
                ['swatch_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $setup->getIdxName(
                    $swatchTable,
                    ['store_id', 'option_id'],
                    true
                ),
                ['store_id', 'option_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName($swatchTable, 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($swatchTable, 'option_id', 'eav_attribute_option', 'option_id'),
                'option_id',
                $setup->getTable('eav_attribute_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Magento Swatches table');

        if (!$setup->getConnection()->tableColumnExists($setup->getTable('catalog_eav_attribute'), 'additional_data')) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('catalog_eav_attribute'),
                    'additional_data',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'default' => null,
                        'nullable' => true,
                        'comment' => 'Additional swatch attributes data',
                    ]
                );
        }

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
