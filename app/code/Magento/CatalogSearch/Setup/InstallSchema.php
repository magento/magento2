<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn(
            $installer->getTable('catalog_eav_attribute'),
            'search_weight',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                'unsigned' => true,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Search Weight'
            ]
        );
        $installer->endSetup();
    }
}
