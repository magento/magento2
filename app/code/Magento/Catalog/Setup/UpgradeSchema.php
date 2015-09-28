<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup;

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
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {

            $fields = [
                ['table' => 'catalog_product_index_price_final_idx', 'column' => 'base_group_price'],
                ['table' => 'catalog_product_index_price_final_tmp', 'column' => 'base_group_price'],
                ['table' => 'catalog_product_index_price', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_cfg_opt_agr_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_cfg_opt_agr_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_cfg_opt_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_cfg_opt_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_final_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_final_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_opt_agr_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_opt_agr_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_opt_idx', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_opt_tmp', 'column' => 'group_price'],
                ['table' => 'catalog_product_index_price_tmp', 'column' => 'group_price'],
            ];

            foreach ($fields as $filedInfo) {
                $connection->dropColumn($setup->getTable($filedInfo['table']), $filedInfo['column']);
            }
        }

        $setup->endSetup();
    }
}
