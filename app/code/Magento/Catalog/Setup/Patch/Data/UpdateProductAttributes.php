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
 * Class UpdateProductAttributes
 * @package Magento\Catalog\Setup\Patch
 */
class UpdateProductAttributes implements DataPatchInterface, PatchVersionInterface
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);

        //Product Details tab
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'status',
            'frontend_label',
            'Enable Product',
            5
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'name',
            'frontend_label',
            'Product Name'
        );
        $attributeSetId = $categorySetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Product Details',
            'visibility',
            80
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Product Details',
            'news_from_date',
            90
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Product Details',
            'news_to_date',
            100
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Product Details',
            'country_of_manufacture',
            110
        );

        //Content tab
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
            'description'
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Content',
            'short_description',
            100
        );

        //Images tab
        $groupId = (int)$categorySetup->getAttributeGroupByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'image-management',
            'attribute_group_id'
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $groupId,
            'image',
            1
        );
        $categorySetup->updateAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            $groupId,
            'attribute_group_name',
            'Images'
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'image',
            'frontend_label',
            'Base'
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'small_image',
            'frontend_label',
            'Small'
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'image',
            'frontend_input_renderer',
            null
        );

        //Design tab
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'page_layout',
            'frontend_label',
            'Layout'
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'custom_layout_update',
            'frontend_label',
            'Layout Update XML',
            10
        );

        //Schedule Design Update tab
        $categorySetup->addAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Schedule Design Update',
            55
        );
        $categorySetup->updateAttributeGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Schedule Design Update',
            'tab_group_code',
            'advanced'
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Schedule Design Update',
            'custom_design_from',
            20
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Schedule Design Update',
            'custom_design_to',
            30
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'custom_design',
            'frontend_label',
            'New Theme',
            40
        );
        $categorySetup->addAttributeToGroup(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeSetId,
            'Schedule Design Update',
            'custom_design'
        );
        $categorySetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'custom_layout',
            [
                'type' => 'varchar',
                'label' => 'New Layout',
                'input' => 'select',
                'source' => \Magento\Catalog\Model\Product\Attribute\Source\Layout::class,
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'Schedule Design Update',
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false
            ]
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
        return '2.0.5';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
