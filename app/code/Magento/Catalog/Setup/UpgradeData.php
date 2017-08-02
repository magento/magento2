<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     * @since 2.0.0
     */
    private $categorySetupFactory;

    /**
     * EAV setup factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     * @since 2.1.0
     */
    private $eavSetupFactory;

    /**
     * @var UpgradeWidgetData
     * @since 2.2.0
     */
    private $upgradeWidgetData;

    /**
     * @var UpgradeWebsiteAttributes
     * @since 2.2.0
     */
    private $upgradeWebsiteAttributes;

    /**
     * Constructor
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param UpgradeWidgetData $upgradeWidgetData
     * @param UpgradeWebsiteAttributes $upgradeWebsiteAttributes
     * @since 2.0.0
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        UpgradeWidgetData $upgradeWidgetData,
        UpgradeWebsiteAttributes $upgradeWebsiteAttributes
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->upgradeWidgetData = $upgradeWidgetData;
        $this->upgradeWebsiteAttributes = $upgradeWebsiteAttributes;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.1') < 0
        ) {
            $select = $setup->getConnection()->select()
                ->from(
                    $setup->getTable('catalog_product_entity_group_price'),
                    [
                        'entity_id',
                        'all_groups',
                        'customer_group_id',
                        new \Zend_Db_Expr('1'),
                        'value',
                        'website_id'
                    ]
                );
            $select = $setup->getConnection()->insertFromSelect(
                $select,
                $setup->getTable('catalog_product_entity_tier_price'),
                [
                    'entity_id',
                    'all_groups',
                    'customer_group_id',
                    'qty',
                    'value',
                    'website_id'
                ]
            );
            $setup->getConnection()->query($select);

            $categorySetupManager = $this->categorySetupFactory->create();
            $categorySetupManager->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'group_price');
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
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
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            /** @var CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->updateAttribute(3, 54, 'default_value', 1);
        }

        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            $mediaBackendType = 'static';
            $mediaBackendModel = null;
            /** @var CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->updateAttribute(
                'catalog_product',
                'media_gallery',
                'backend_type',
                $mediaBackendType
            );
            $categorySetup->updateAttribute(
                'catalog_product',
                'media_gallery',
                'backend_model',
                $mediaBackendModel
            );
        }

        if (version_compare($context->getVersion(), '2.0.5', '<')) {
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
        }

        if (version_compare($context->getVersion(), '2.0.7') < 0) {
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'meta_description',
                [
                    'note' => 'Maximum 255 chars. Meta Description should optimally be between 150-160 characters'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.1.3') < 0) {
            /** @var CategorySetup $categorySetup */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $this->changePriceAttributeDefaultScope($categorySetup);
        }

        if (version_compare($context->getVersion(), '2.1.5') < 0) {
            $this->disallowUsingHtmlForProductName($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.2.1') < 0) {
            $this->upgradeWidgetData->upgrade();
        }

        if (version_compare($context->getVersion(), '2.2.2') < 0) {
            $this->upgradeWebsiteAttributes->upgrade($setup);
        }

        $setup->endSetup();
    }

    /**
     * Set to 'No' 'Is Allowed Html on Store Front' option on product name attribute, because product name
     * is multi entity field (used in order, quote) and cannot be conditionally escaped in all places
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     * @since 2.2.0
     */
    private function disallowUsingHtmlForProductName(ModuleDataSetupInterface $setup)
    {
        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attribute = $categorySetup->getAttribute($entityTypeId, 'name');

        $setup->getConnection()
            ->update(
                $setup->getTable('catalog_eav_attribute'),
                ['is_html_allowed_on_front' => 0],
                $setup->getConnection()->quoteInto('attribute_id = ?', $attribute['attribute_id'])
            );
    }

    /**
     * @param CategorySetup $categorySetup
     * @return void
     * @since 2.2.0
     */
    private function changePriceAttributeDefaultScope($categorySetup)
    {
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        foreach (['price', 'cost', 'special_price'] as $attributeCode) {
            $attribute = $categorySetup->getAttribute($entityTypeId, $attributeCode);
            if (isset($attribute['attribute_id'])) {
                $categorySetup->updateAttribute(
                    $entityTypeId,
                    $attribute['attribute_id'],
                    'is_global',
                    \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
                );
            }
        }
    }
}
