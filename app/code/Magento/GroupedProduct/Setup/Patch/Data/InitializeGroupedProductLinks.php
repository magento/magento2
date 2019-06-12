<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InitializeGroupedProductLinks
 * @package Magento\GroupedProduct\Setup\Patch
 */
class InitializeGroupedProductLinks implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InitializeGroupedProductLinks constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
         * Install grouped product link type
         */
        $data = [
            'link_type_id' => \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED,
            'code' => 'super',
        ];
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('catalog_product_link_type'),
            $data
        );

        /**
         * Install grouped product link attributes
         */
        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from(
                ['c' => $this->moduleDataSetup->getTable('catalog_product_link_attribute')]
            )
            ->where(
                "c.link_type_id=?",
                \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
            );
        $result = $this->moduleDataSetup->getConnection()->fetchAll($select);
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
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable('catalog_product_link_attribute'),
                $data
            );
        }
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $field = 'country_of_manufacture';
        $applyTo = explode(',', $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to'));
        if (!in_array('grouped', $applyTo)) {
            $applyTo[] = 'grouped';
            $eavSetup->updateAttribute(Product::ENTITY, $field, 'apply_to', implode(',', $applyTo));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
