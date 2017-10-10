<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Eav module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->addUniqueKeyToEavAttributeGroupTable($setup);
        }

        if (version_compare($context->getVersion(), '2.1.1', '<')) {
            $this->modifyAttributeCodeColumnForNotNullable($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addUniqueKeyToEavAttributeGroupTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable('eav_attribute_group'),
            $setup->getIdxName(
                'catalog_category_product',
                ['attribute_set_id', 'attribute_group_code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['attribute_set_id', 'attribute_group_code'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * Column attribute_code from eav_attribute should not be nullable.
     *
     * @param SchemaSetupInterface $setup
     */
    private function modifyAttributeCodeColumnForNotNullable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable('eav_attribute'),
            'attribute_code',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Attribute Code',
            ]
        );
    }
}
