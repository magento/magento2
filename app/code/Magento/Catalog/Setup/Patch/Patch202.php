<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202
{


    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();


            // set new resource model paths
            /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
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


        $setup->endSetup();

    }

}
