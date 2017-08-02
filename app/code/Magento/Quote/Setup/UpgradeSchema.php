<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 * @since 2.0.0
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private static $connectionName = 'checkout';

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $setup->getConnection(self::$connectionName)->addIndex(
                $setup->getTable('quote_id_mask', self::$connectionName),
                $setup->getIdxName('quote_id_mask', ['masked_id'], '', self::$connectionName),
                ['masked_id']
            );
        }

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $setup->getConnection(self::$connectionName)->changeColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'street',
                'street',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Street'
                ]
            );
        }
        //drop foreign key for single DB case
        if (version_compare($context->getVersion(), '2.0.3', '<')
            && $setup->tableExists($setup->getTable('quote_item'))
        ) {
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('quote_item'),
                $setup->getFkName('quote_item', 'product_id', 'catalog_product_entity', 'entity_id')
            );
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection = $setup->getConnection();
            $connection->modifyColumn(
                $setup->getTable('quote_address'),
                'shipping_method',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 120
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $connection = $setup->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'firstname',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                ]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'middlename',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 40,
                ]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'lastname',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                ]
            )->modifyColumn(
                $setup->getTable('quote', self::$connectionName),
                'updated_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                ]
            );
        }
        $setup->endSetup();
    }
}
