<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Setup\Patch\Data;

use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Helper\DefaultCategoryFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class InstallDefaultCategories data patch.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallDefaultCategories implements DataPatchInterface, PatchVersionInterface
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
     * @var DefaultCategoryFactory
     */
    private $defaultCategoryFactory;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     * @param DefaultCategoryFactory $defaultCategoryFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        \Magento\Catalog\Helper\DefaultCategoryFactory $defaultCategoryFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->defaultCategoryFactory = $defaultCategoryFactory;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function apply()
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $rootCategoryId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        $defaultCategory = $this->defaultCategoryFactory->create();
        $defaultCategoryId = $defaultCategory->getId();

        $categorySetup->installEntities();
        // Create Root Catalog Node
        $categorySetup->createCategory()
            ->load($rootCategoryId)
            ->setId($rootCategoryId)
            ->setStoreId(0)
            ->setPath($rootCategoryId)
            ->setLevel(0)
            ->setPosition(0)
            ->setChildrenCount(0)
            ->setName('Root Catalog')
            ->setInitialSetupFlag(true)
            ->save();

        // Create Default Catalog Node
        $category = $categorySetup->createCategory();
        $category->load($defaultCategoryId)
            ->setId($defaultCategoryId)
            ->setStoreId(0)
            ->setPath($rootCategoryId . '/' . $defaultCategoryId)
            ->setName('Default Category')
            ->setDisplayMode('PRODUCTS')
            ->setIsActive(1)
            ->setLevel(1)
            ->setInitialSetupFlag(true)
            ->setAttributeSetId($category->getDefaultAttributeSetId())
            ->save();

        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => \Magento\Catalog\Helper\Category::XML_PATH_CATEGORY_ROOT_ID,
            'value' => $category->getId(),
        ];
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('core_config_data'),
            $data,
            ['value']
        );

        $categorySetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', 'Design', 6);

        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $attributeGroupId = $categorySetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

        // update General Group
        $categorySetup->updateAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'sort_order', '10');

        $groups = [
            'content' => ['name' => 'Content', 'code' => 'cms-content', 'sort' => 10, 'id' => null],
            'display' => ['name' => 'Display Settings', 'code' => 'display-settings', 'sort' => 20, 'id' => null],
            'design' => ['name' => 'Custom Design', 'code' => 'custom-design', 'sort' => 30, 'id' => null],
        ];

        foreach ($groups as $k => $groupProp) {
            $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupProp['name'], $groupProp['sort']);
            $groups[$k]['id'] = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupProp['code']);
        }

        // update attributes group and sort
        $attributes = [
            'custom_design' => ['group' => 'design', 'sort' => 10],
            'custom_design_from' => ['group' => 'design', 'sort' => 20],
            'custom_design_to' => ['group' => 'design', 'sort' => 30],
            'page_layout' => ['group' => 'design', 'sort' => 40],
            'custom_layout_update' => ['group' => 'design', 'sort' => 50],
            'display_mode' => ['group' => 'display', 'sort' => 10],
            'landing_page' => ['group' => 'content', 'sort' => 10],
            'is_anchor' => ['group' => 'display', 'sort' => 20],
            'available_sort_by' => ['group' => 'display', 'sort' => 30],
            'default_sort_by' => ['group' => 'display', 'sort' => 40],
        ];

        foreach ($attributes as $attributeCode => $attributeProp) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groups[$attributeProp['group']]['id'],
                $attributeCode,
                $attributeProp['sort']
            );
        }

        /**
         * Install product link types
         */
        $data = [
            ['link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED, 'code' => 'relation'],
            ['link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL, 'code' => 'up_sell'],
            ['link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL, 'code' => 'cross_sell'],
        ];

        foreach ($data as $bind) {
            $this->moduleDataSetup->getConnection()->insertForce(
                $this->moduleDataSetup->getTable(
                    'catalog_product_link_type'
                ),
                $bind
            );
        }

        /**
         * install product link attributes
         */
        $data = [
            [
                'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_RELATED,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ],
            [
                'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_UPSELL,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int'
            ],
            [
                'link_type_id' => \Magento\Catalog\Model\Product\Link::LINK_TYPE_CROSSSELL,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int'
            ],
        ];

        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('catalog_product_link_attribute'),
            $data
        );

        /**
         * Remove Catalog specified attribute options (columns) from eav/attribute table
         *
         */
        $describe = $this->moduleDataSetup->getConnection()
            ->describeTable($this->moduleDataSetup->getTable('catalog_eav_attribute'));
        foreach ($describe as $columnData) {
            if ($columnData['COLUMN_NAME'] == 'attribute_id') {
                continue;
            }
            $this->moduleDataSetup->getConnection()->dropColumn(
                $this->moduleDataSetup->getTable('eav_attribute'),
                $columnData['COLUMN_NAME']
            );
        }

        $newGeneralTabName = 'Product Details';
        $newPriceTabName = 'Advanced Pricing';
        $newImagesTabName = 'Image Management';
        $newMetaTabName = 'Search Engine Optimization';
        $autosettingsTabName = 'Autosettings';
        $tabNames = [
            'General' => [
                'attribute_group_name' => $newGeneralTabName,
                'attribute_group_code' => $categorySetup->convertToAttributeGroupCode($newGeneralTabName),
                'tab_group_code' => 'basic',
                'sort_order' => 10,
            ],
            'Images' => [
                'attribute_group_name' => $newImagesTabName,
                'attribute_group_code' => $categorySetup->convertToAttributeGroupCode($newImagesTabName),
                'tab_group_code' => 'basic',
                'sort_order' => 20,
            ],
            'Meta Information' => [
                'attribute_group_name' => $newMetaTabName,
                'attribute_group_code' => $categorySetup->convertToAttributeGroupCode($newMetaTabName),
                'tab_group_code' => 'basic',
                'sort_order' => 30,
            ],
            'Prices' => [
                'attribute_group_name' => $newPriceTabName,
                'attribute_group_code' => $categorySetup->convertToAttributeGroupCode($newPriceTabName),
                'tab_group_code' => 'advanced',
                'sort_order' => 40,
            ],
            'Design' => ['attribute_group_code' => 'design', 'tab_group_code' => 'advanced', 'sort_order' => 50],
        ];

        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $categorySetup->getAttributeSetId($entityTypeId, 'Default');

        //Rename attribute tabs
        foreach ($tabNames as $tabName => $tab) {
            $groupId = $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, $tabName);
            if ($groupId) {
                foreach ($tab as $propertyName => $propertyValue) {
                    $categorySetup->updateAttributeGroup(
                        $entityTypeId,
                        $attributeSetId,
                        $groupId,
                        $propertyName,
                        $propertyValue
                    );
                }
            }
        }

        //Add new tab
        $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, $autosettingsTabName, 60);
        $categorySetup->updateAttributeGroup(
            $entityTypeId,
            $attributeSetId,
            'Autosettings',
            'attribute_group_code',
            'autosettings'
        );
        $categorySetup->updateAttributeGroup(
            $entityTypeId,
            $attributeSetId,
            'Autosettings',
            'tab_group_code',
            'advanced'
        );

        //New attributes order and properties
        $properties = ['is_required', 'default_value', 'frontend_input_renderer'];
        $attributesOrder = [
            //Product Details tab
            'name' => [$newGeneralTabName => 10],
            'sku' => [$newGeneralTabName => 20],
            'price' => [$newGeneralTabName => 30],
            'image' => [$newGeneralTabName => 50],
            'weight' => [$newGeneralTabName => 70, 'is_required' => 0],
            'category_ids' => [$newGeneralTabName => 80],
            'description' => [$newGeneralTabName => 90, 'is_required' => 0],
            'status' => [
                $newGeneralTabName => 100,
                'is_required' => 0,
                'default_value' => 1,
                'frontend_input_renderer' => \Magento\Framework\Data\Form\Element\Hidden::class,
            ],
            //Autosettings tab
            'short_description' => [$autosettingsTabName => 0, 'is_required' => 0],
            'visibility' => [$autosettingsTabName => 20, 'is_required' => 0],
            'news_from_date' => [$autosettingsTabName => 30],
            'news_to_date' => [$autosettingsTabName => 40],
            'country_of_manufacture' => [$autosettingsTabName => 50],
        ];

        foreach ($attributesOrder as $key => $value) {
            $attribute = $categorySetup->getAttribute($entityTypeId, $key);
            if ($attribute) {
                foreach ($value as $propertyName => $propertyValue) {
                    if (in_array($propertyName, $properties)) {
                        $categorySetup->updateAttribute(
                            $entityTypeId,
                            $attribute['attribute_id'],
                            $propertyName,
                            $propertyValue
                        );
                    } else {
                        $categorySetup->addAttributeToGroup(
                            $entityTypeId,
                            $attributeSetId,
                            $propertyName,
                            $attribute['attribute_id'],
                            $propertyValue
                        );
                    }
                }
            }
        }

        foreach (['status', 'visibility'] as $attributeCode) {
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeCode,
                'is_required_in_admin_store',
                '1'
            );
        }
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'custom_design_from',
            'attribute_model',
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        );
        $categorySetup->updateAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'custom_design_from',
            'frontend_model',
            \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime::class
        );
        
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
