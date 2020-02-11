<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Extra comments install schema class.
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('customer_extra_abilities')
        )
            ->addColumn(
                'ability_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Ability Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'customer Id'
            )
            ->addColumn(
                'is_allowed_add_description',
                Table::TYPE_INTEGER,
                255,
                ['nullable' => false],
                'Extra comment'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'customer_extra_abilities',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Customer extra abilities'
            );
        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()->newTable(
            $installer->getTable('product_extra_comments')
        )
            ->addColumn(
                'comment_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Ability Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'customer Id'
            )
            ->addColumn(
                'product_sku',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Product SKU'
            )
            ->addColumn(
                'is_approved',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Is approved or not'
            )
            ->addColumn(
                'extra_comment',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Extra comment'
            )
            ->addIndex(
                $setup->getIdxName('product_extra_comments', ['extra_comment']),
                ['extra_comment']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'product_extra_comments',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'product_extra_comments',
                    'product_sku',
                    'catalog_product_entity',
                    'sku'
                ),
                'product_sku',
                $installer->getTable('catalog_product_entity'),
                'sku',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Extra comments'
            );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
