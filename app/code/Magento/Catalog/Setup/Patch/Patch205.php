<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Setup\Patch;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch205 implements \Magento\Setup\Model\Patch\DataPatchInterface
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
    public function apply(ModuleDataSetupInterface $setup)
    {
        $setup->startSetup();


        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

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


        $setup->endSetup();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


}
