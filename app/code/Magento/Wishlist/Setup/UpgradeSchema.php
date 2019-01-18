<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Upgrade Schema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $connection->addForeignKey(
                $connection->getForeignKeyName(
                    $setup->getTable('wishlist_item_option'),
                    'product_id',
                    $setup->getTable('catalog_product_entity'),
                    'entity_id'
                ),
                $setup->getTable('wishlist_item_option'),
                'product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                AdapterInterface::FK_ACTION_CASCADE,
                true
            );
        }
        $setup->endSetup();
    }
}
