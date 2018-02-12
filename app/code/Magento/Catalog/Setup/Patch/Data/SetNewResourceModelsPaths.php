<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class SetNewResourceModelsPaths
 * @package Magento\Catalog\Setup\Patch
 */
class SetNewResourceModelsPaths implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * PatchInitial constructor.
     * @param ResourceConnection $resourceConnection
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        // set new resource model paths
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['resourceConnection' => $this->resourceConnection]);
        $categorySetup->updateEntityType(
            \Magento\Catalog\Model\Category::ENTITY,
            'entity_model',
            \Magento\Catalog\Model\ResourceModel\Category::class
        );
        $categorySetup->updateEntityType(
            \Magento\Catalog\Model\Category::ENTITY,
            'attribute_model',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        );
        $categorySetup->updateEntityType(
            \Magento\Catalog\Model\Category::ENTITY,
            'entity_attribute_collection',
            \Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection::class
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'custom_design_from',
            'attribute_model',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        );
        $categorySetup->updateEntityType(
            \Magento\Catalog\Model\Product::ENTITY,
            'entity_model',
            \Magento\Catalog\Model\ResourceModel\Product::class
        );
        $categorySetup->updateEntityType(
            \Magento\Catalog\Model\Product::ENTITY,
            'attribute_model',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        );
        $categorySetup->updateEntityType(
            \Magento\Catalog\Model\Product::ENTITY,
            'entity_attribute_collection',
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class
        );

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            RemoveGroupPrice::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
