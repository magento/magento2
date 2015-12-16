<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the CatalogRule module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->removeSubProductDiscounts($setup);
        }

        $setup->endSetup();
    }

    /**
     * Remove Sub Product Discounts
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeSubProductDiscounts(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $fields = [
            'table' => 'catalogrule',
            'columns' => [
                'sub_is_enable',
                'sub_simple_action',
                'sub_discount_amount'
            ]
        ];

        foreach ($fields['columns'] as $filedInfo) {
            $connection->dropColumn($setup->getTable($fields['table']), $filedInfo);
        }
    }
}
