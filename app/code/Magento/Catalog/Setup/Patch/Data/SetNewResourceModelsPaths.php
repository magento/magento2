<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class SetNewResourceModelsPaths
 * @package Magento\Catalog\Setup\Patch
 */
class SetNewResourceModelsPaths implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        // set new resource model paths
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
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
            InstallDefaultCategories::class,
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
