<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\System\Config;
use Mtf\Handler\HandlerFactory;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;
use Mtf\Repository\RepositoryFactory;
use Mtf\System\Event\EventManagerInterface;

/**
 * Class CatalogProductSimple
 * Product Simple fixture
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class CatalogProductSimple extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Catalog\Test\Repository\CatalogProductSimple';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Catalog\Test\Handler\CatalogProductSimple\CatalogProductSimpleInterface';

    /**
     * Constructor
     *
     * @constructor
     * @param Config $configuration
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param HandlerFactory $handlerFactory
     * @param EventManagerInterface $eventManager
     * @param array $data
     * @param string $dataSet
     * @param bool $persist
     */
    public function __construct(
        Config $configuration,
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        HandlerFactory $handlerFactory,
        EventManagerInterface $eventManager,
        array $data = [],
        $dataSet = '',
        $persist = false
    ) {
        parent::__construct(
            $configuration,
            $repositoryFactory,
            $fixtureFactory,
            $handlerFactory,
            $eventManager,
            $data,
            $dataSet,
            $persist
        );
        if (!isset($this->data['url_key']) && isset($this->data['name'])) {
            $this->data['url_key'] = trim(strtolower(preg_replace('#[^0-9a-z%]+#i', '-', $this->data['name'])), '-');
        }
    }

    protected $dataConfig = [
        'type_id' => 'simple',
        'create_url_params' => [
            'type' => 'simple',
            'set' => '4',
        ],
        'input_prefix' => 'product',
    ];

    protected $defaultDataSet = [
        'name' => 'Test simple product %isolation%',
        'sku' => 'test_simple_sku_%isolation%',
        'attribute_set_id' => ['dataSet' => 'default'],
        'price' => ['value' => 100.00],
        'weight' => 12.0000,
        'quantity_and_stock_status' => [
            'qty' => 10.0000,
            'is_in_stock' => 'In Stock',
        ],
        'website_ids' => ['Main Website'],
    ];

    protected $category_ids = [
        'attribute_code' => 'category_ids',
        'backend_type' => 'static',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'product-details',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\CategoryIds',
    ];

    protected $color = [
        'attribute_code' => 'color',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $country_of_manufacture = [
        'attribute_code' => 'country_of_manufacture',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $created_at = [
        'attribute_code' => 'created_at',
        'backend_type' => 'static',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $custom_design = [
        'attribute_code' => 'custom_design',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $custom_design_from = [
        'attribute_code' => 'custom_design_from',
        'backend_type' => 'datetime',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'date',
    ];

    protected $custom_design_to = [
        'attribute_code' => 'custom_design_to',
        'backend_type' => 'datetime',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'date',
    ];

    protected $custom_layout_update = [
        'attribute_code' => 'custom_layout_update',
        'backend_type' => 'text',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'textarea',
    ];

    protected $description = [
        'attribute_code' => 'description',
        'backend_type' => 'text',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'textarea',
        'group' => 'product-details',
    ];

    protected $gallery = [
        'attribute_code' => 'gallery',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'gallery',
    ];

    protected $gift_message_available = [
        'attribute_code' => 'gift_message_available',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $group_price = [
        'attribute_code' => 'group_price',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'advanced-pricing',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\GroupPriceOptions',
    ];

    protected $has_options = [
        'attribute_code' => 'has_options',
        'backend_type' => 'static',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $image = [
        'attribute_code' => 'image',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'media_image',
    ];

    protected $image_label = [
        'attribute_code' => 'image_label',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $manufacturer = [
        'attribute_code' => 'manufacturer',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $media_gallery = [
        'attribute_code' => 'media_gallery',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'gallery',
    ];

    protected $meta_description = [
        'attribute_code' => 'meta_description',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'group' => 'search-engine-optimization',
        'input' => 'textarea',
    ];

    protected $meta_keyword = [
        'attribute_code' => 'meta_keyword',
        'backend_type' => 'text',
        'is_required' => '0',
        'default_value' => '',
        'group' => 'search-engine-optimization',
        'input' => 'textarea',
    ];

    protected $meta_title = [
        'attribute_code' => 'meta_title',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'group' => 'search-engine-optimization',
        'input' => 'text',
    ];

    protected $minimal_price = [
        'attribute_code' => 'minimal_price',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'price',
    ];

    protected $msrp = [
        'attribute_code' => 'msrp',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'price',
    ];

    protected $msrp_display_actual_price_type = [
        'attribute_code' => 'msrp_display_actual_price_type',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => 'Use config',
        'input' => 'select',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
        'group' => 'product-details',
    ];

    protected $old_id = [
        'attribute_code' => 'old_id',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $options_container = [
        'attribute_code' => 'options_container',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => 'Block after Info Column',
        'input' => 'select',
    ];

    protected $page_layout = [
        'attribute_code' => 'page_layout',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'select',
    ];

    protected $price = [
        'attribute_code' => 'price',
        'backend_type' => 'decimal',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'price',
        'group' => 'product-details',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\Price',
    ];

    protected $quantity_and_stock_status = [
        'attribute_code' => 'quantity_and_stock_status',
        'backend_type' => 'array',
        'is_required' => '0',
        'default_value' => '',
        'input' => '',
        'group' => 'product-details',
    ];

    protected $required_options = [
        'attribute_code' => 'required_options',
        'backend_type' => 'static',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $sku = [
        'attribute_code' => 'sku',
        'backend_type' => 'static',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
        'group' => 'product-details',
    ];

    protected $small_image = [
        'attribute_code' => 'small_image',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'media_image',
    ];

    protected $small_image_label = [
        'attribute_code' => 'small_image_label',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $special_from_date = [
        'attribute_code' => 'special_from_date',
        'backend_type' => 'datetime',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'date',
    ];

    protected $short_description = [
        'attribute_code' => 'short_description',
        'backend_type' => 'text',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'textarea',
        'group' => 'autosettings',
    ];

    protected $special_price = [
        'attribute_code' => 'special_price',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'price',
        'group' => 'advanced-pricing',
    ];

    protected $special_to_date = [
        'attribute_code' => 'special_to_date',
        'backend_type' => 'datetime',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'date',
    ];

    protected $status = [
        'attribute_code' => 'status',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => 'Product online',
        'input' => 'checkbox',
        'group' => 'product-details',
    ];

    protected $tax_class_id = [
        'attribute_code' => 'tax_class_id',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => 'Taxable Goods',
        'input' => 'select',
        'group' => 'product-details',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\TaxClass',
    ];

    protected $thumbnail = [
        'attribute_code' => 'thumbnail',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'media_image',
    ];

    protected $thumbnail_label = [
        'attribute_code' => 'thumbnail_label',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $tier_price = [
        'attribute_code' => 'tier_price',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'advanced-pricing',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\TierPriceOptions',
    ];

    protected $updated_at = [
        'attribute_code' => 'updated_at',
        'backend_type' => 'static',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $url_key = [
        'attribute_code' => 'url_key',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'search-engine-optimization',
    ];

    protected $url_path = [
        'attribute_code' => 'url_path',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $visibility = [
        'attribute_code' => 'visibility',
        'backend_type' => 'int',
        'is_required' => '0',
        'default_value' => 'Catalog, Search',
        'input' => 'select',
        'group' => 'autosettings',
    ];

    protected $weight = [
        'attribute_code' => 'weight',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'weight',
        'group' => 'product-details',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'virtual',
        'group' => null,
    ];

    protected $type_id = [
        'attribute_code' => 'type_id',
        'backend_type' => 'virtual',
    ];

    protected $attribute_set_id = [
        'attribute_code' => 'attribute_set_id',
        'backend_type' => 'virtual',
        'group' => 'product-details',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\AttributeSetId',
    ];

    protected $custom_attribute = [
        'attribute_code' => 'custom_attribute',
        'backend_type' => 'virtual',
        'group' => 'product-details',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\CustomAttribute',
    ];

    protected $custom_options = [
        'attribute_code' => 'custom_options',
        'backend_type' => 'virtual',
        'is_required' => '0',
        'group' => 'customer-options',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\CustomOptions',
    ];

    protected $website_ids = [
        'attribute_code' => 'website_ids',
        'backend_type' => 'virtual',
        'default_value' => 'Main Website',
        'group' => 'websites',
    ];

    protected $is_returnable = [
        'attribute_code' => 'is_returnable',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '2',
        'input' => 'select',
        'group' => 'autosettings',
    ];

    protected $news_from_date = [
        'attribute_code' => 'news_from_date',
        'backend_type' => 'datetime',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'date',
        'source' => 'Magento\Backend\Test\Fixture\Date',
    ];

    protected $news_to_date = [
        'attribute_code' => 'news_to_date',
        'backend_type' => 'datetime',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'date',
        'source' => 'Magento\Backend\Test\Fixture\Date',
    ];

    protected $stock_data = [
        'attribute_code' => 'stock_data',
        'backend_type' => 'virtual',
        'group' => 'advanced-inventory'
    ];

    protected $checkout_data = [
        'attribute_code' => 'checkout_data',
        'backend_type' => 'virtual',
        'group' => null,
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\CheckoutData'
    ];

    protected $cross_sell_products = [
        'attribute_code' => 'cross_sell_products',
        'backend_type' => 'virtual',
        'group' => 'crosssells',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\CrossSellProducts'
    ];

    protected $up_sell_products = [
        'attribute_code' => 'up_sell_products',
        'backend_type' => 'virtual',
        'group' => 'upsells',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\UpSellProducts'
    ];

    protected $related_products = [
        'attribute_code' => 'related_products',
        'backend_type' => 'virtual',
        'group' => 'related-products',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\RelatedProducts'
    ];

    protected $is_virtual = [
        'attribute_code' => 'is_virtual',
        'backend_type' => 'virtual',
        'group' => 'product-details',
    ];

    protected $attributes = [
        'attribute_code' => 'attributes',
        'backend_type' => 'virtual',
    ];

    protected $fpt = [
        'attribute_code' => 'fpt',
        'backend_type' => 'decimal',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'text',
        'group' => 'product-details',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductSimple\Fpt'
    ];

    public function getCategoryIds()
    {
        return $this->getData('category_ids');
    }

    public function getColor()
    {
        return $this->getData('color');
    }

    public function getCountryOfManufacture()
    {
        return $this->getData('country_of_manufacture');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getCustomDesign()
    {
        return $this->getData('custom_design');
    }

    public function getCustomDesignFrom()
    {
        return $this->getData('custom_design_from');
    }

    public function getCustomDesignTo()
    {
        return $this->getData('custom_design_to');
    }

    public function getCustomLayoutUpdate()
    {
        return $this->getData('custom_layout_update');
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    public function getGallery()
    {
        return $this->getData('gallery');
    }

    public function getGiftMessageAvailable()
    {
        return $this->getData('gift_message_available');
    }

    public function getGroupPrice()
    {
        return $this->getData('group_price');
    }

    public function getHasOptions()
    {
        return $this->getData('has_options');
    }

    public function getImage()
    {
        return $this->getData('image');
    }

    public function getImageLabel()
    {
        return $this->getData('image_label');
    }

    public function getManufacturer()
    {
        return $this->getData('manufacturer');
    }

    public function getMediaGallery()
    {
        return $this->getData('media_gallery');
    }

    public function getMetaDescription()
    {
        return $this->getData('meta_description');
    }

    public function getMetaKeyword()
    {
        return $this->getData('meta_keyword');
    }

    public function getMetaTitle()
    {
        return $this->getData('meta_title');
    }

    public function getMinimalPrice()
    {
        return $this->getData('minimal_price');
    }

    public function getMsrp()
    {
        return $this->getData('msrp');
    }

    public function getMsrpDisplayActualPriceType()
    {
        return $this->getData('msrp_display_actual_price_type');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getOldId()
    {
        return $this->getData('old_id');
    }

    public function getOptionsContainer()
    {
        return $this->getData('options_container');
    }

    public function getPageLayout()
    {
        return $this->getData('page_layout');
    }

    public function getPrice()
    {
        return $this->getData('price');
    }

    public function getQuantityAndStockStatus()
    {
        return $this->getData('quantity_and_stock_status');
    }

    public function getRequiredOptions()
    {
        return $this->getData('required_options');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getSmallImage()
    {
        return $this->getData('small_image');
    }

    public function getSmallImageLabel()
    {
        return $this->getData('small_image_label');
    }

    public function getSpecialFromDate()
    {
        return $this->getData('special_from_date');
    }

    public function getShortDescription()
    {
        return $this->getData('short_description');
    }

    public function getSpecialPrice()
    {
        return $this->getData('special_price');
    }

    public function getSpecialToDate()
    {
        return $this->getData('special_to_date');
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function getTaxClassId()
    {
        return $this->getData('tax_class_id');
    }

    public function getThumbnail()
    {
        return $this->getData('thumbnail');
    }

    public function getThumbnailLabel()
    {
        return $this->getData('thumbnail_label');
    }

    public function getTierPrice()
    {
        return $this->getData('tier_price');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function getUrlKey()
    {
        return $this->getData('url_key');
    }

    public function getUrlPath()
    {
        return $this->getData('url_path');
    }

    public function getVisibility()
    {
        return $this->getData('visibility');
    }

    public function getWeight()
    {
        return $this->getData('weight');
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function getTypeId()
    {
        return $this->getData('type_id');
    }

    public function getAttributeSetId()
    {
        return $this->getData('attribute_set_id');
    }

    public function getCustomAttribute()
    {
        return $this->getData('custom_attribute');
    }

    public function getCustomOptions()
    {
        return $this->getData('custom_options');
    }

    public function getWebsiteIds()
    {
        return $this->getData('website_ids');
    }

    public function getIsReturnable()
    {
        return $this->getData('is_returnable');
    }

    public function getNewsFromDate()
    {
        return $this->getData('news_from_date');
    }

    public function getNewsToDate()
    {
        return $this->getData('news_to_date');
    }

    public function getStockData()
    {
        return $this->getData('stock_data');
    }

    public function getCheckoutData()
    {
        return $this->getData('checkout_data');
    }

    public function getCrossSellProducts()
    {
        return $this->getData('cross_sell_products');
    }

    public function getUpSellProducts()
    {
        return $this->getData('up_sell_products');
    }

    public function getRelatedProducts()
    {
        return $this->getData('related_products');
    }

    public function getIsVirtual()
    {
        return $this->getData('is_virtual');
    }

    public function getAttributes()
    {
        return $this->getData('attributes');
    }

    public function getFptData()
    {
        return $this->getData('fpt');
    }
}
