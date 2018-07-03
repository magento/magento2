<?php
/**
 * Catalog entity setup
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Setup;

use Magento\Catalog\Block\Adminhtml\Category\Helper\Pricestep;
use Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\Available;
use Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\DefaultSortby;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category as CategoryFormHelper;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight as WeightFormHelper;
use Magento\Catalog\Model\Attribute\Backend\Customlayoutupdate;
use Magento\Catalog\Model\Attribute\Backend\Startdate;
use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Magento\Catalog\Model\Category\Attribute\Backend\Sortby as SortbyBackendModel;
use Magento\Catalog\Model\Category\Attribute\Source\Layout;
use Magento\Catalog\Model\Category\Attribute\Source\Mode;
use Magento\Catalog\Model\Category\Attribute\Source\Page;
use Magento\Catalog\Model\Category\Attribute\Source\Sortby;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Entity\Product\Attribute\Design\Options\Container;
use Magento\Catalog\Model\Product\Attribute\Backend\Category as CategoryBackendAttribute;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Catalog\Model\Product\Attribute\Backend\Stock;
use Magento\Catalog\Model\Product\Attribute\Backend\Tierprice;
use Magento\Catalog\Model\Product\Attribute\Backend\Weight;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image as ImageFrontendModel;
use Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture;
use Magento\Catalog\Model\Product\Attribute\Source\Layout as LayoutModel;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogInventory\Block\Adminhtml\Form\Field\Stock as StockField;
use Magento\CatalogInventory\Model\Source\Stock as StockSourceModel;
use Magento\CatalogInventory\Model\Stock as StockModel;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Theme\Model\Theme\Source\Theme;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategorySetup extends EavSetup
{
    /**
     * Category model factory
     *
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * This should be set explicitly
     */
    const CATEGORY_ENTITY_TYPE_ID = 3;

    /**
     * This should be set explicitly
     */
    const CATALOG_PRODUCT_ENTITY_TYPE_ID = 4;

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * Creates category model
     *
     * @param array $data
     * @return \Magento\Catalog\Model\Category
     * @codeCoverageIgnore
     */
    public function createCategory($data = [])
    {
        return $this->categoryFactory->create($data);
    }

    /**
     * Default entities and attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultEntities()
    {
        return [
            'catalog_category' => [
                'entity_type_id' => self::CATEGORY_ENTITY_TYPE_ID,
                'entity_model' => Category::class,
                'attribute_model' => Attribute::class,
                'table' => 'catalog_category_entity',
                'additional_attribute_table' => 'catalog_eav_attribute',
                'entity_attribute_collection' =>
                    Collection::class,
                'attributes' => [
                    'name' => [
                        'type' => 'varchar',
                        'label' => 'Name',
                        'input' => 'text',
                        'sort_order' => 1,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'is_active' => [
                        'type' => 'int',
                        'label' => 'Is Active',
                        'input' => 'select',
                        'source' => Boolean::class,
                        'sort_order' => 2,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'description' => [
                        'type' => 'text',
                        'label' => 'Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 4,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'group' => 'General Information',
                    ],
                    'image' => [
                        'type' => 'varchar',
                        'label' => 'Image',
                        'input' => 'image',
                        'backend' => Image::class,
                        'required' => false,
                        'sort_order' => 5,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'meta_title' => [
                        'type' => 'varchar',
                        'label' => 'Page Title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 6,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'meta_keywords' => [
                        'type' => 'text',
                        'label' => 'Meta Keywords',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 7,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'meta_description' => [
                        'type' => 'text',
                        'label' => 'Meta Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 8,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'display_mode' => [
                        'type' => 'varchar',
                        'label' => 'Display Mode',
                        'input' => 'select',
                        'source' => Mode::class,
                        'required' => false,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Display Settings',
                    ],
                    'landing_page' => [
                        'type' => 'int',
                        'label' => 'CMS Block',
                        'input' => 'select',
                        'source' => Page::class,
                        'required' => false,
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Display Settings',
                    ],
                    'is_anchor' => [
                        'type' => 'int',
                        'label' => 'Is Anchor',
                        'input' => 'select',
                        'source' => Boolean::class,
                        'required' => false,
                        'sort_order' => 30,
                        'group' => 'Display Settings',
                    ],
                    'path' => [
                        'type' => 'static',
                        'label' => 'Path',
                        'required' => false,
                        'sort_order' => 12,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'position' => [
                        'type' => 'static',
                        'label' => 'Position',
                        'required' => false,
                        'sort_order' => 13,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'all_children' => [
                        'type' => 'text',
                        'required' => false,
                        'sort_order' => 14,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'path_in_store' => [
                        'type' => 'text',
                        'required' => false,
                        'sort_order' => 15,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'children' => [
                        'type' => 'text',
                        'required' => false,
                        'sort_order' => 16,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'custom_design' => [
                        'type' => 'varchar',
                        'label' => 'Custom Design',
                        'input' => 'select',
                        'source' => Theme::class,
                        'required' => false,
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'custom_design_from' => [
                        'type' => 'datetime',
                        'label' => 'Active From',
                        'input' => 'date',
                        'backend' => Startdate::class,
                        'required' => false,
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'custom_design_to' => [
                        'type' => 'datetime',
                        'label' => 'Active To',
                        'input' => 'date',
                        'backend' => Datetime::class,
                        'required' => false,
                        'sort_order' => 40,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'page_layout' => [
                        'type' => 'varchar',
                        'label' => 'Page Layout',
                        'input' => 'select',
                        'source' => Layout::class,
                        'required' => false,
                        'sort_order' => 50,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'custom_layout_update' => [
                        'type' => 'text',
                        'label' => 'Custom Layout Update',
                        'input' => 'textarea',
                        'backend' => Customlayoutupdate::class,
                        'required' => false,
                        'sort_order' => 60,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'level' => [
                        'type' => 'static',
                        'label' => 'Level',
                        'required' => false,
                        'sort_order' => 24,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'children_count' => [
                        'type' => 'static',
                        'label' => 'Children Count',
                        'required' => false,
                        'sort_order' => 25,
                        'visible' => false,
                        'group' => 'General Information',
                    ],
                    'available_sort_by' => [
                        'type' => 'text',
                        'label' => 'Available Product Listing Sort By',
                        'input' => 'multiselect',
                        'source' => Sortby::class,
                        'backend' => SortbyBackendModel::class,
                        'sort_order' => 40,
                        'input_renderer' => Available::class,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Display Settings',
                    ],
                    'default_sort_by' => [
                        'type' => 'varchar',
                        'label' => 'Default Product Listing Sort By',
                        'input' => 'select',
                        'source' => Sortby::class,
                        'backend' => SortbyBackendModel::class,
                        'sort_order' => 50,
                        'input_renderer' =>
                            DefaultSortby::class,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Display Settings',
                    ],
                    'include_in_menu' => [
                        'type' => 'int',
                        'label' => 'Include in Navigation Menu',
                        'input' => 'select',
                        'source' => Boolean::class,
                        'default' => '1',
                        'sort_order' => 10,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'General Information',
                    ],
                    'custom_use_parent_settings' => [
                        'type' => 'int',
                        'label' => 'Use Parent Category Settings',
                        'input' => 'select',
                        'source' => Boolean::class,
                        'required' => false,
                        'sort_order' => 5,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'custom_apply_to_products' => [
                        'type' => 'int',
                        'label' => 'Apply To Products',
                        'input' => 'select',
                        'source' => Boolean::class,
                        'required' => false,
                        'sort_order' => 6,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Custom Design',
                    ],
                    'filter_price_range' => [
                        'type' => 'decimal',
                        'label' => 'Layered Navigation Price Step',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 51,
                        'input_renderer' => Pricestep::class,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Display Settings',
                    ],
                ],
            ],
            'catalog_product' => [
                'entity_type_id' => self::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'entity_model' => Product::class,
                'attribute_model' => Attribute::class,
                'table' => 'catalog_product_entity',
                'additional_attribute_table' => 'catalog_eav_attribute',
                'entity_attribute_collection' =>
                    Product\Attribute\Collection::class,
                'attributes' => [
                    'name' => [
                        'type' => 'varchar',
                        'label' => 'Name',
                        'input' => 'text',
                        'frontend_class' => 'validate-length maximum-length-255',
                        'sort_order' => 1,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'searchable' => true,
                        'visible_in_advanced_search' => true,
                        'used_in_product_listing' => true,
                        'used_for_sort_by' => true,
                    ],
                    'sku' => [
                        'type' => 'static',
                        'label' => 'SKU',
                        'input' => 'text',
                        'frontend_class' => 'validate-length maximum-length-64',
                        'backend' => Sku::class,
                        'unique' => true,
                        'sort_order' => 2,
                        'searchable' => true,
                        'comparable' => true,
                        'visible_in_advanced_search' => true,
                    ],
                    'description' => [
                        'type' => 'text',
                        'label' => 'Description',
                        'input' => 'textarea',
                        'sort_order' => 3,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'searchable' => true,
                        'comparable' => true,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'visible_in_advanced_search' => true,
                    ],
                    'short_description' => [
                        'type' => 'text',
                        'label' => 'Short Description',
                        'input' => 'textarea',
                        'sort_order' => 4,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'searchable' => true,
                        'comparable' => true,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'visible_in_advanced_search' => true,
                        'used_in_product_listing' => true,
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'price' => [
                        'type' => 'decimal',
                        'label' => 'Price',
                        'input' => 'price',
                        'backend' => Price::class,
                        'sort_order' => 1,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'searchable' => true,
                        'filterable' => true,
                        'visible_in_advanced_search' => true,
                        'used_in_product_listing' => true,
                        'used_for_sort_by' => true,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                    ],
                    'special_price' => [
                        'type' => 'decimal',
                        'label' => 'Special Price',
                        'input' => 'price',
                        'backend' => Price::class,
                        'required' => false,
                        'sort_order' => 3,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'used_in_product_listing' => true,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'special_from_date' => [
                        'type' => 'datetime',
                        'label' => 'Special Price From Date',
                        'input' => 'date',
                        'backend' => Startdate::class,
                        'required' => false,
                        'sort_order' => 4,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'used_in_product_listing' => true,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'special_to_date' => [
                        'type' => 'datetime',
                        'label' => 'Special Price To Date',
                        'input' => 'date',
                        'backend' => Datetime::class,
                        'required' => false,
                        'sort_order' => 5,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'used_in_product_listing' => true,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'cost' => [
                        'type' => 'decimal',
                        'label' => 'Cost',
                        'input' => 'price',
                        'backend' => Price::class,
                        'required' => false,
                        'user_defined' => true,
                        'sort_order' => 6,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'weight' => [
                        'type' => 'decimal',
                        'label' => 'Weight',
                        'input' => 'weight',
                        'backend' => Weight::class,
                        'input_renderer' => WeightFormHelper::class,
                        'sort_order' => 5,
                        'apply_to' => 'simple,virtual',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'manufacturer' => [
                        'type' => 'int',
                        'label' => 'Manufacturer',
                        'input' => 'select',
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'comparable' => true,
                        'visible_in_advanced_search' => true,
                        'apply_to' => Type::TYPE_SIMPLE,
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'meta_title' => [
                        'type' => 'varchar',
                        'label' => 'Meta Title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 20,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Meta Information',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'meta_keyword' => [
                        'type' => 'text',
                        'label' => 'Meta Keywords',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 30,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Meta Information',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'meta_description' => [
                        'type' => 'varchar',
                        'label' => 'Meta Description',
                        'input' => 'textarea',
                        'required' => false,
                        'note' => 'Maximum 255 chars',
                        'class' => 'validate-length maximum-length-255',
                        'sort_order' => 40,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Meta Information',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'image' => [
                        'type' => 'varchar',
                        'label' => 'Base Image',
                        'input' => 'media_image',
                        'frontend' => ImageFrontendModel::class,
                        'input_renderer' => BaseImage::class,
                        'required' => false,
                        'sort_order' => 0,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'used_in_product_listing' => true,
                        'group' => 'General',
                    ],
                    'small_image' => [
                        'type' => 'varchar',
                        'label' => 'Small Image',
                        'input' => 'media_image',
                        'frontend' => ImageFrontendModel::class,
                        'required' => false,
                        'sort_order' => 2,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'used_in_product_listing' => true,
                        'group' => 'Images',
                    ],
                    'thumbnail' => [
                        'type' => 'varchar',
                        'label' => 'Thumbnail',
                        'input' => 'media_image',
                        'frontend' => ImageFrontendModel::class,
                        'required' => false,
                        'sort_order' => 3,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'used_in_product_listing' => true,
                        'group' => 'Images',
                    ],
                    'media_gallery' => [
                        'type' => 'varchar',
                        'label' => 'Media Gallery',
                        'input' => 'gallery',
                        'backend' => Media::class,
                        'required' => false,
                        'sort_order' => 4,
                        'group' => 'Images',
                    ],
                    'old_id' => ['type' => 'int', 'required' => false, 'sort_order' => 6, 'visible' => false],
                    'tier_price' => [
                        'type' => 'decimal',
                        'label' => 'Tier Price',
                        'input' => 'text',
                        'backend' => Tierprice::class,
                        'required' => false,
                        'sort_order' => 7,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                    ],
                    'color' => [
                        'type' => 'int',
                        'label' => 'Color',
                        'input' => 'select',
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'comparable' => true,
                        'visible_in_advanced_search' => true,
                        'apply_to' => implode(',', [Type::TYPE_SIMPLE, Type::TYPE_VIRTUAL]),
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'news_from_date' => [
                        'type' => 'datetime',
                        'label' => 'Set Product as New from Date',
                        'input' => 'date',
                        'backend' => Startdate::class,
                        'required' => false,
                        'sort_order' => 7,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'used_in_product_listing' => true,
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'news_to_date' => [
                        'type' => 'datetime',
                        'label' => 'Set Product as New to Date',
                        'input' => 'date',
                        'backend' => Datetime::class,
                        'required' => false,
                        'sort_order' => 8,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'used_in_product_listing' => true,
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'gallery' => [
                        'type' => 'varchar',
                        'label' => 'Image Gallery',
                        'input' => 'gallery',
                        'required' => false,
                        'sort_order' => 5,
                        'group' => 'Images',
                    ],
                    'status' => [
                        'type' => 'int',
                        'label' => 'Status',
                        'input' => 'select',
                        'source' => Status::class,
                        'sort_order' => 9,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'searchable' => true,
                        'used_in_product_listing' => true,
                    ],
                    'minimal_price' => [
                        'type' => 'decimal',
                        'label' => 'Minimal Price',
                        'input' => 'price',
                        'required' => false,
                        'sort_order' => 8,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'visible' => false,
                        'apply_to' => 'simple,virtual',
                        'group' => 'Prices',
                    ],
                    'visibility' => [
                        'type' => 'int',
                        'label' => 'Visibility',
                        'input' => 'select',
                        'source' => Visibility::class,
                        'default' => '4',
                        'sort_order' => 12,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                    ],
                    'custom_design' => [
                        'type' => 'varchar',
                        'label' => 'Custom Design',
                        'input' => 'select',
                        'source' => Theme::class,
                        'required' => false,
                        'sort_order' => 1,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Design',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'custom_design_from' => [
                        'type' => 'datetime',
                        'label' => 'Active From',
                        'input' => 'date',
                        'backend' => Startdate::class,
                        'required' => false,
                        'sort_order' => 2,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Design',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'custom_design_to' => [
                        'type' => 'datetime',
                        'label' => 'Active To',
                        'input' => 'date',
                        'backend' => Datetime::class,
                        'required' => false,
                        'sort_order' => 3,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Design',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'custom_layout_update' => [
                        'type' => 'text',
                        'label' => 'Custom Layout Update',
                        'input' => 'textarea',
                        'backend' => Customlayoutupdate::class,
                        'required' => false,
                        'sort_order' => 4,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Design',
                    ],
                    'page_layout' => [
                        'type' => 'varchar',
                        'label' => 'Page Layout',
                        'input' => 'select',
                        'source' => LayoutModel::class,
                        'required' => false,
                        'sort_order' => 5,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Design',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'category_ids' => [
                        'type' => 'static',
                        'label' => 'Categories',
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'backend' => CategoryBackendAttribute::class,
                        'input_renderer' => CategoryFormHelper::class,
                        'required' => false,
                        'sort_order' => 9,
                        'visible' => true,
                        'group' => 'General',
                        'is_used_in_grid' => false,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                    ],
                    'options_container' => [
                        'type' => 'varchar',
                        'label' => 'Display Product Options In',
                        'input' => 'select',
                        'source' => Container::class,
                        'required' => false,
                        'default' => 'container2',
                        'sort_order' => 6,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'group' => 'Design',
                    ],
                    'required_options' => [
                        'type' => 'static',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 14,
                        'visible' => false,
                        'used_in_product_listing' => true,
                    ],
                    'has_options' => [
                        'type' => 'static',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 15,
                        'visible' => false,
                    ],
                    'image_label' => [
                        'type' => 'varchar',
                        'label' => 'Image Label',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 16,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'visible' => false,
                        'used_in_product_listing' => true,
                    ],
                    'small_image_label' => [
                        'type' => 'varchar',
                        'label' => 'Small Image Label',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 17,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'visible' => false,
                        'used_in_product_listing' => true,
                    ],
                    'thumbnail_label' => [
                        'type' => 'varchar',
                        'label' => 'Thumbnail Label',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 18,
                        'global' => ScopedAttributeInterface::SCOPE_STORE,
                        'visible' => false,
                        'used_in_product_listing' => true,
                    ],
                    'created_at' => [
                        'type' => 'static',
                        'input' => 'date',
                        'sort_order' => 19,
                        'visible' => false,
                    ],
                    'updated_at' => [
                        'type' => 'static',
                        'input' => 'date',
                        'sort_order' => 20,
                        'visible' => false,
                    ],
                    'country_of_manufacture' => [
                        'type' => 'varchar',
                        'label' => 'Country of Manufacture',
                        'input' => 'select',
                        'source' => Countryofmanufacture::class,
                        'required' => false,
                        'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                        'visible' => true,
                        'user_defined' => false,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'unique' => false,
                        'apply_to' => 'simple,bundle',
                        'group' => 'General',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => true,
                    ],
                    'quantity_and_stock_status' => [
                        'group' => 'General',
                        'type' => 'int',
                        'backend' => Stock::class,
                        'label' => 'Quantity',
                        'input' => 'select',
                        'input_renderer' => StockField::class,
                        'source' => StockSourceModel::class,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'default' => StockModel::STOCK_IN_STOCK,
                        'user_defined' => false,
                        'visible' => true,
                        'required' => false,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'unique' => false,
                    ],
                ],
            ]
        ];
    }
}
