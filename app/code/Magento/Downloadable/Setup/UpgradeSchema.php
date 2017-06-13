<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $tables = [
                'catalog_product_index_price_downlod_idx',
                'catalog_product_index_price_downlod_tmp',
            ];
            foreach ($tables as $table) {
                $setup->getConnection()->modifyColumn(
                    $setup->getTable($table),
                    'customer_group_id',
                    ['type' => 'integer', 'nullable' => false]
                );
            }
        }

        $setup->endSetup();
    }
}
