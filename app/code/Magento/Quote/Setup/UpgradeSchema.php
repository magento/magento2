<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var string
     */
    private static $connectionName = 'checkout';

    /**
     * {@inheritdoc}
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
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Street'
                ]
            );
        }
        //drop foreign key for single DB case
        if (version_compare($context->getVersion(), '2.0.3', '<')
            && $setup->tableExists($setup->getTable('quote_item', self::$connectionName))
        ) {
            $setup->getConnection(self::$connectionName)->dropForeignKey(
                $setup->getTable('quote_item', self::$connectionName),
                $setup->getFkName('quote_item', 'product_id', 'catalog_product_entity', 'entity_id')
            );
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection = $setup->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'shipping_method',
                [
                    'type' => Table::TYPE_TEXT,
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
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                ]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'middlename',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 40,
                ]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'lastname',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                ]
            )->modifyColumn(
                $setup->getTable('quote', self::$connectionName),
                'updated_at',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE,
                ]
            );
        }
        if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $connection = $setup->getConnection(self::$connectionName);
            $connection->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'telephone',
                ['type' => Table::TYPE_TEXT, 'length' => 255]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'fax',
                ['type' => Table::TYPE_TEXT, 'length' => 255]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'region',
                ['type' => Table::TYPE_TEXT, 'length' => 255]
            )->modifyColumn(
                $setup->getTable('quote_address', self::$connectionName),
                'city',
                ['type' => Table::TYPE_TEXT, 'length' => 255]
            );
        }
        $setup->endSetup();
    }
}
