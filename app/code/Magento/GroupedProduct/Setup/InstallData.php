<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * Install grouped product link type
         */
        $data = [
            'link_type_id' => \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
            'code' => 'super',
        ];
        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('catalog_product_link_type'), $data);

        /**
         * Install grouped product link attributes
         */
        $select = $setup->getConnection()
            ->select()
            ->from(
                ['c' => $setup->getTable('catalog_product_link_attribute')]
            )
            ->where(
                "c.link_type_id=?",
                \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
            );
        $result = $setup->getConnection()->fetchAll($select);

        if (!$result) {
            $data = [
                [
                    'link_type_id' => \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
                    'product_link_attribute_code' => 'position',
                    'data_type' => 'int',
                ],
                [
                    'link_type_id' => \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
                    'product_link_attribute_code' => 'qty',
                    'data_type' => 'decimal'
                ],
            ];

            $setup->getConnection()->insertMultiple($setup->getTable('catalog_product_link_attribute'), $data);
        }

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $field = 'country_of_manufacture';
        $applyTo = explode(',', $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to'));
        if (!in_array('grouped', $applyTo)) {
            $applyTo[] = 'grouped';
            $eavSetup->updateAttribute(Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
        }
    }
}
