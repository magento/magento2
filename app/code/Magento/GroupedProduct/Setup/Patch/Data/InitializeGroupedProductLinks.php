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
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class InitializeGroupedProductLinks
 * @package Magento\GroupedProduct\Setup\Patch
 */
class InitializeGroupedProductLinks implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InitializeGroupedProductLinks constructor.
     * @param ResourceConnection $resourceConnection
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
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
        $this->resourceConnection->getConnection()->insertOnDuplicate(
            $this->resourceConnection->getConnection()->getTableName('catalog_product_link_type'),
            $data
        );

        /**
         * Install grouped product link attributes
         */
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from(
                ['c' => $this->resourceConnection->getConnection()->getTableName('catalog_product_link_attribute')]
            )
            ->where(
                "c.link_type_id=?",
                \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED
            );
        $result = $this->resourceConnection->getConnection()->fetchAll($select);
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
            $this->resourceConnection->getConnection()->insertMultiple(
                $this->resourceConnection->getConnection()->getTableName('catalog_product_link_attribute'),
                $data
            );
        }
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['resourceConnection' => $this->resourceConnection]);
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
    public function getVersion()
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
