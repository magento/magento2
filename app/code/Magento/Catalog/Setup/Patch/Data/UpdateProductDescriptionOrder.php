<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class UpdateProductDescriptionOrder
 *
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateProductDescriptionOrder implements DataPatchInterface
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
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);

        // Content
        $categorySetup->addAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Content',
            15
        );
        $categorySetup->updateAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Content',
            'tab_group_code',
            'basic'
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Content',
            'short_description',
            100
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Content',
            'description',
            110
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateMediaAttributesBackendTypes::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.3.5';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
