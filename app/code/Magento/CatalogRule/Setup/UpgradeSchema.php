<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $tables = [
                'catalogrule_product',
                'catalogrule_product_price',
            ];
            foreach ($tables as $table) {
                $setup->getConnection()->modifyColumn(
                    $setup->getTable($table),
                    'customer_group_id',
                    ['type' => 'integer']
                );
            }
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
        $data = [
            'catalogrule' => [
                'sub_is_enable',
                'sub_simple_action',
                'sub_discount_amount',
            ],
            'catalogrule_product' => [
                'sub_simple_action',
                'sub_discount_amount',
            ],
        ];

        foreach ($data as $table => $fields) {
            foreach ($fields as $field) {
                $connection->dropColumn($setup->getTable($table), $field);
            }
        }
    }
}
