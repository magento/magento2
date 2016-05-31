<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Setup\Model\Complex\Pattern;
use Magento\Setup\Model\Complex\Generator;

/**
 * Class ConfigurableProductsFixture
 */
class ConfigurableProductsFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 50;

    //@codingStandardsIgnoreStart
    /**
     * Get CSV template headers
     * @SuppressWarnings(PHPMD)
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'sku',
            'store_view_code',
            'attribute_set_code',
            'product_type',
            'categories',
            'product_websites',
            'color',
            'configurable_variation',
            'cost',
            'country_of_manufacture',
            'created_at',
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update',
            'description',
            'enable_googlecheckout',
            'gallery',
            'gift_message_available',
            'gift_wrapping_available',
            'gift_wrapping_price',
            'has_options',
            'image',
            'image_label',
            'is_returnable',
            'manufacturer',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'minimal_price',
            'msrp',
            'msrp_display_actual_price_type',
            'name',
            'news_from_date',
            'news_to_date',
            'options_container',
            'page_layout',
            'price',
            'quantity_and_stock_status',
            'related_tgtr_position_behavior',
            'related_tgtr_position_limit',
            'required_options',
            'short_description',
            'small_image',
            'small_image_label',
            'special_from_date',
            'special_price',
            'special_to_date',
            'product_online',
            'tax_class_name',
            'thumbnail',
            'thumbnail_label',
            'updated_at',
            'upsell_tgtr_position_behavior',
            'upsell_tgtr_position_limit',
            'url_key',
            'url_path',
            'variations',
            'variations_1382710717',
            'variations_1382710773',
            'variations_1382710861',
            'visibility',
            'weight',
            'qty',
            'min_qty',
            'use_config_min_qty',
            'is_qty_decimal',
            'backorders',
            'use_config_backorders',
            'min_sale_qty',
            'use_config_min_sale_qty',
            'max_sale_qty',
            'use_config_max_sale_qty',
            'is_in_stock',
            'notify_stock_qty',
            'use_config_notify_stock_qty',
            'manage_stock',
            'use_config_manage_stock',
            'use_config_qty_increments',
            'qty_increments',
            'use_config_enable_qty_inc',
            'enable_qty_increments',
            'is_decimal_divided',
            '_related_sku',
            '_related_position',
            '_crosssell_sku',
            '_crosssell_position',
            '_upsell_sku',
            '_upsell_position',
            '_associated_sku',
            '_associated_default_qty',
            '_associated_position',
            '_tier_price_website',
            '_tier_price_customer_group',
            '_tier_price_qty',
            '_tier_price_price',
            '_media_attribute_id',
            '_media_image',
            '_media_label',
            '_media_position',
            '_media_is_disabled',
            '_super_products_sku',
            '_super_attribute_code',
            '_super_attribute_option',
            'configurable_variations',
        ];
    }

    /**
     * Get CSV template rows
     *
     * @param Closure|mixed $productCategory
     * @param Closure|mixed $productWebsite
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return array
     */
    protected function getRows($productCategory, $productWebsite)
    {
        return [
            [
                'sku' => 'Configurable Product %s-option 1',
                'store_view_code' => '',
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'categories' => $productCategory,
                'product_websites' => $productWebsite,
                'color' => '',
                'configurable_variation' => 'option 1',
                'cost' => '',
                'country_of_manufacture' => '',
                'created_at' => '2013-10-25 15:12:32',
                'custom_design' => '',
                'custom_design_from' => '',
                'custom_design_to' => '',
                'custom_layout_update' => '',
                'description' => '<p>Configurable product description %s</p>',
                'enable_googlecheckout' => '1',
                'gallery' => '',
                'gift_message_available' => '',
                'gift_wrapping_available' => '',
                'gift_wrapping_price' => '',
                'has_options' => '0',
                'image' => '',
                'image_label' => '',
                'is_returnable' => 'no',
                'manufacturer' => '',
                'meta_description' => 'Configurable Product %s <p>Configurable product description 1</p>',
                'meta_keyword' => 'Configurable Product 1',
                'meta_title' => 'Configurable Product %s',
                'minimal_price' => '',
                'msrp' => '',
                'msrp_display_actual_price_type' => 'Use config',
                'name' => 'Configurable Product %s-option 1',
                'news_from_date' => '',
                'news_to_date' => '',
                'options_container' => 'Block after Info Column',
                'page_layout' => '',
                'price' => '10',
                'quantity_and_stock_status' => 'In Stock',
                'related_tgtr_position_behavior' => '',
                'related_tgtr_position_limit' => '',
                'required_options' => '0',
                'short_description' => '',
                'small_image' => '',
                'small_image_label' => '',
                'special_from_date' => '',
                'special_price' => '',
                'special_to_date' => '',
                'product_online' => '1',
                'tax_class_name' => 'Taxable Goods',
                'thumbnail' => '',
                'thumbnail_label' => '',
                'updated_at' => '2013-10-25 15:12:32',
                'upsell_tgtr_position_behavior' => '',
                'upsell_tgtr_position_limit' => '',
                'url_key' => 'configurable-product-%s-option-1',
                'url_path' => 'configurable-product-%s-option-1',
                'variations' => '',
                'variations_1382710717' => '',
                'variations_1382710773' => '',
                'variations_1382710861' => '',
                'visibility' => 'Not Visible Individually',
                'weight' => '1',
                'qty' => '111.0000',
                'min_qty' => '0.0000',
                'use_config_min_qty' => '1',
                'is_qty_decimal' => '0',
                'backorders' => '0',
                'use_config_backorders' => '1',
                'min_sale_qty' => '1.0000',
                'use_config_min_sale_qty' => '1',
                'max_sale_qty' => '0.0000',
                'use_config_max_sale_qty' => '1',
                'is_in_stock' => '1',
                'notify_stock_qty' => '',
                'use_config_notify_stock_qty' => '1',
                'manage_stock' => '1',
                'use_config_manage_stock' => '1',
                'use_config_qty_increments' => '1',
                'qty_increments' => '0.0000',
                'use_config_enable_qty_inc' => '1',
                'enable_qty_increments' => '0',
                'is_decimal_divided' => '0',
                '_related_sku' => '',
                '_related_position' => '',
                '_crosssell_sku' => '',
                '_crosssell_position' => '',
                '_upsell_sku' => '',
                '_upsell_position' => '',
                '_associated_sku' => '',
                '_associated_default_qty' => '',
                '_associated_position' => '',
                '_tier_price_website' => '',
                '_tier_price_customer_group' => '',
                '_tier_price_qty' => '',
                '_tier_price_price' => '',
                '_media_attribute_id' => '',
                '_media_image' => '',
                '_media_label' => '',
                '_media_position' => '',
                '_media_is_disabled' => '',
                '_super_products_sku' => '',
                '_super_attribute_code' => '',
                '_super_attribute_option' => '',
            ],
            [
                'sku' => 'Configurable Product %s-option 2',
                'store_view_code' => '',
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'categories' => $productCategory,
                'product_websites' => $productWebsite,
                'color' => '',
                'configurable_variation' => 'option 2',
                'cost' => '',
                'country_of_manufacture' => '',
                'created_at' => '2013-10-25 15:12:35',
                'custom_design' => '',
                'custom_design_from' => '',
                'custom_design_to' => '',
                'custom_layout_update' => '',
                'description' => '<p>Configurable product description %s</p>',
                'enable_googlecheckout' => '1',
                'gallery' => '',
                'gift_message_available' => '',
                'gift_wrapping_available' => '',
                'gift_wrapping_price' => '',
                'has_options' => '0',
                'image' => '',
                'image_label' => '',
                'is_returnable' => 'no',
                'manufacturer' => '',
                'meta_description' => 'Configurable Product %s <p>Configurable product description 1</p>',
                'meta_keyword' => 'Configurable Product 1',
                'meta_title' => 'Configurable Product %s',
                'minimal_price' => '',
                'msrp' => '',
                'msrp_display_actual_price_type' => 'Use config',
                'name' => 'Configurable Product %s-option 2',
                'news_from_date' => '',
                'news_to_date' => '',
                'options_container' => 'Block after Info Column',
                'page_layout' => '',
                'price' => '10',
                'quantity_and_stock_status' => 'In Stock',
                'related_tgtr_position_behavior' => '',
                'related_tgtr_position_limit' => '',
                'required_options' => '0',
                'short_description' => '',
                'small_image' => '',
                'small_image_label' => '',
                'special_from_date' => '',
                'special_price' => '',
                'special_to_date' => '',
                'product_online' => '1',
                'tax_class_name' => 'Taxable Goods',
                'thumbnail' => '',
                'thumbnail_label' => '',
                'updated_at' => '2013-10-25 15:12:35',
                'upsell_tgtr_position_behavior' => '',
                'upsell_tgtr_position_limit' => '',
                'url_key' => 'configurable-product-%s-option-2',
                'url_path' => 'configurable-product-%s-option-2',
                'variations' => '',
                'variations_1382710717' => '',
                'variations_1382710773' => '',
                'variations_1382710861' => '',
                'visibility' => 'Not Visible Individually',
                'weight' => '1',
                'qty' => '111.0000',
                'min_qty' => '0.0000',
                'use_config_min_qty' => '1',
                'is_qty_decimal' => '0',
                'backorders' => '0',
                'use_config_backorders' => '1',
                'min_sale_qty' => '1.0000',
                'use_config_min_sale_qty' => '1',
                'max_sale_qty' => '0.0000',
                'use_config_max_sale_qty' => '1',
                'is_in_stock' => '1',
                'notify_stock_qty' => '',
                'use_config_notify_stock_qty' => '1',
                'manage_stock' => '1',
                'use_config_manage_stock' => '1',
                'use_config_qty_increments' => '1',
                'qty_increments' => '0.0000',
                'use_config_enable_qty_inc' => '1',
                'enable_qty_increments' => '0',
                'is_decimal_divided' => '0',
                '_related_sku' => '',
                '_related_position' => '',
                '_crosssell_sku' => '',
                '_crosssell_position' => '',
                '_upsell_sku' => '',
                '_upsell_position' => '',
                '_associated_sku' => '',
                '_associated_default_qty' => '',
                '_associated_position' => '',
                '_tier_price_website' => '',
                '_tier_price_customer_group' => '',
                '_tier_price_qty' => '',
                '_tier_price_price' => '',
                '_media_attribute_id' => '',
                '_media_image' => '',
                '_media_label' => '',
                '_media_position' => '',
                '_media_is_disabled' => '',
            ],
            [
                'sku' => 'Configurable Product %s-option 3',
                'store_view_code' => '',
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'categories' => $productCategory,
                'product_websites' => $productWebsite,
                'color' => '',
                'configurable_variation' => 'option 3',
                'cost' => '',
                'country_of_manufacture' => '',
                'created_at' => '2013-10-25 15:12:37',
                'custom_design' => '',
                'custom_design_from' => '',
                'custom_design_to' => '',
                'custom_layout_update' => '',
                'description' => '<p>Configurable product description %s</p>',
                'enable_googlecheckout' => '1',
                'gallery' => '',
                'gift_message_available' => '',
                'gift_wrapping_available' => '',
                'gift_wrapping_price' => '',
                'has_options' => '0',
                'image' => '',
                'image_label' => '',
                'is_returnable' => 'no',
                'manufacturer' => '',
                'meta_description' => 'Configurable Product %s <p>Configurable product description 1</p>',
                'meta_keyword' => 'Configurable Product 1',
                'meta_title' => 'Configurable Product %s',
                'minimal_price' => '',
                'msrp' => '',
                'msrp_display_actual_price_type' => 'Use config',
                'name' => 'Configurable Product %s-option 3',
                'news_from_date' => '',
                'news_to_date' => '',
                'options_container' => 'Block after Info Column',
                'page_layout' => '',
                'price' => '10',
                'quantity_and_stock_status' => 'In Stock',
                'related_tgtr_position_behavior' => '',
                'related_tgtr_position_limit' => '',
                'required_options' => '0',
                'short_description' => '',
                'small_image' => '',
                'small_image_label' => '',
                'special_from_date' => '',
                'special_price' => '',
                'special_to_date' => '',
                'product_online' => '1',
                'tax_class_name' => 'Taxable Goods',
                'thumbnail' => '',
                'thumbnail_label' => '',
                'updated_at' => '2013-10-25 15:12:37',
                'upsell_tgtr_position_behavior' => '',
                'upsell_tgtr_position_limit' => '',
                'url_key' => 'configurable-product-%s-option-3',
                'url_path' => 'configurable-product-%s-option-3',
                'variations' => '',
                'variations_1382710717' => '',
                'variations_1382710773' => '',
                'variations_1382710861' => '',
                'visibility' => 'Not Visible Individually',
                'weight' => '1',
                'qty' => '111.0000',
                'min_qty' => '0.0000',
                'use_config_min_qty' => '1',
                'is_qty_decimal' => '0',
                'backorders' => '0',
                'use_config_backorders' => '1',
                'min_sale_qty' => '1.0000',
                'use_config_min_sale_qty' => '1',
                'max_sale_qty' => '0.0000',
                'use_config_max_sale_qty' => '1',
                'is_in_stock' => '1',
                'notify_stock_qty' => '',
                'use_config_notify_stock_qty' => '1',
                'manage_stock' => '1',
                'use_config_manage_stock' => '1',
                'use_config_qty_increments' => '1',
                'qty_increments' => '0.0000',
                'use_config_enable_qty_inc' => '1',
                'enable_qty_increments' => '0',
                'is_decimal_divided' => '0',
                '_related_sku' => '',
                '_related_position' => '',
                '_crosssell_sku' => '',
                '_crosssell_position' => '',
                '_upsell_sku' => '',
                '_upsell_position' => '',
                '_associated_sku' => '',
                '_associated_default_qty' => '',
                '_associated_position' => '',
                '_tier_price_website' => '',
                '_tier_price_customer_group' => '',
                '_tier_price_qty' => '',
                '_tier_price_price' => '',
                '_media_attribute_id' => '',
                '_media_image' => '',
                '_media_label' => '',
                '_media_position' => '',
                '_media_is_disabled' => '',
            ],
            [
                'sku' => 'Configurable Product %s',
                'store_view_code' => '',
                'attribute_set_code' => 'Default',
                'product_type' => 'configurable',
                'categories' => $productCategory,
                'product_websites' => $productWebsite,
                'color' => '',
                'configurable_variation' => '',
                'cost' => '',
                'country_of_manufacture' => '',
                'created_at' => '2013-10-25 15:12:39',
                'custom_design' => '',
                'custom_design_from' => '',
                'custom_design_to' => '',
                'custom_layout_update' => '',
                'description' => '<p>Configurable product description %s</p>',
                'enable_googlecheckout' => '1',
                'gallery' => '',
                'gift_message_available' => '',
                'gift_wrapping_available' => '',
                'gift_wrapping_price' => '',
                'has_options' => '1',
                'image' => '',
                'image_label' => '',
                'is_returnable' => 'no',
                'manufacturer' => '',
                'meta_description' => 'Configurable Product %s <p>Configurable product description %s</p>',
                'meta_keyword' => 'Configurable Product %s',
                'meta_title' => 'Configurable Product %s',
                'minimal_price' => '',
                'msrp' => '',
                'msrp_display_actual_price_type' => 'Use config',
                'name' => 'Configurable Product %s',
                'news_from_date' => '',
                'news_to_date' => '',
                'options_container' => 'Block after Info Column',
                'page_layout' => '',
                'price' => '10',
                'quantity_and_stock_status' => 'In Stock',
                'related_tgtr_position_behavior' => '',
                'related_tgtr_position_limit' => '',
                'required_options' => '1',
                'short_description' => '',
                'small_image' => '',
                'small_image_label' => '',
                'special_from_date' => '',
                'special_price' => '',
                'special_to_date' => '',
                'product_online' => '1',
                'tax_class_name' => 'Taxable Goods',
                'thumbnail' => '',
                'thumbnail_label' => '',
                'updated_at' => '2013-10-25 15:12:39',
                'upsell_tgtr_position_behavior' => '',
                'upsell_tgtr_position_limit' => '',
                'url_key' => 'configurable-product-%s',
                'url_path' => 'configurable-product-%s',
                'variations' => '',
                'variations_1382710717' => '',
                'variations_1382710773' => '',
                'variations_1382710861' => '',
                'visibility' => 'Catalog, Search',
                'weight' => '',
                'qty' => 333,
                'min_qty' => '0.0000',
                'use_config_min_qty' => '1',
                'is_qty_decimal' => '0',
                'backorders' => '0',
                'use_config_backorders' => '1',
                'min_sale_qty' => '1.0000',
                'use_config_min_sale_qty' => '1',
                'max_sale_qty' => '0.0000',
                'use_config_max_sale_qty' => '1',
                'is_in_stock' => '1',
                'notify_stock_qty' => '',
                'use_config_notify_stock_qty' => '1',
                'manage_stock' => '1',
                'use_config_manage_stock' => '1',
                'use_config_qty_increments' => '1',
                'qty_increments' => '0.0000',
                'use_config_enable_qty_inc' => '1',
                'enable_qty_increments' => '0',
                'is_decimal_divided' => '0',
                '_related_sku' => '',
                '_related_position' => '',
                '_crosssell_sku' => '',
                '_crosssell_position' => '',
                '_upsell_sku' => '',
                '_upsell_position' => '',
                '_associated_sku' => '',
                '_associated_default_qty' => '',
                '_associated_position' => '',
                '_tier_price_website' => '',
                '_tier_price_customer_group' => '',
                '_tier_price_qty' => '',
                '_tier_price_price' => '',
                '_media_attribute_id' => '',
                '_media_image' => '',
                '_media_label' => '',
                '_media_position' => '',
                '_media_is_disabled' => '',
                'configurable_variations' => 'sku=Configurable Product %s-option 1,configurable_variation=option 1|sku=Configurable Product %s-option 2,configurable_variation=option 2|sku=Configurable Product %s-option 3,configurable_variation=option 3',
            ],
        ];

    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $configurablesCount = $this->fixtureModel->getValue('configurable_products', 0);
        if (!$configurablesCount) {
            return;
        }
        $this->fixtureModel->resetObjectManager();

        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->create('Magento\Store\Model\StoreManager');
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->get('Magento\Catalog\Model\Category');

        $result = [];
        //Get all websites
        $websites = $storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteCode = $website->getCode();
            //Get all groups
            $websiteGroups = $website->getGroups();
            foreach ($websiteGroups as $websiteGroup) {
                $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
                $category->load($websiteGroupRootCategory);
                $categoryResource = $category->getResource();
                $rootCategoryName = $category->getName();
                //Get all categories
                $resultsCategories = $categoryResource->getAllChildren($category);
                foreach ($resultsCategories as $resultsCategory) {
                    $category->load($resultsCategory);
                    $structure = explode('/', $category->getPath());
                    $pathSize  = count($structure);
                    if ($pathSize > 1) {
                        $path = [];
                        for ($i = 1; $i < $pathSize; $i++) {
                            $path[] = $category->load($structure[$i])->getName();
                        }
                        array_shift($path);
                        $resultsCategoryName = implode('/', $path);
                    } else {
                        $resultsCategoryName = $category->getName();
                    }
                    //Deleted root categories
                    if (trim($resultsCategoryName) != '') {
                        $result[$resultsCategory] = [$websiteCode, $resultsCategoryName, $rootCategoryName];
                    }
                }
            }
        }
        $result = array_values($result);

        $productWebsite = function ($index) use ($result) {
            return $result[$index % count($result)][0];
        };
        $productCategory = function ($index) use ($result) {
            return $result[$index % count($result)][2] . '/' . $result[$index % count($result)][1];
        };

        /**
         * Create configurable products
         */
        $pattern = new Pattern();
        $pattern->setHeaders($this->getHeaders());
        $pattern->setRowsSet($this->getRows($productCategory, $productWebsite));

        /** @var \Magento\ImportExport\Model\Import $import */
        $import = $this->fixtureModel->getObjectManager()->create(
            'Magento\ImportExport\Model\Import',
            [
                'data' => [
                    'entity' => 'catalog_product',
                    'behavior' => 'append',
                    'validation_strategy' => 'validation-stop-on-errors'
                ]
            ]
        );

        $source = new Generator($pattern, $configurablesCount);
        // it is not obvious, but the validateSource() will actually save import queue data to DB
        $import->validateSource($source);
        // this converts import queue into actual entities
        $import->importSource();

    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating configurable products';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'configurable_products' => 'Configurable products'
        ];
    }
}
