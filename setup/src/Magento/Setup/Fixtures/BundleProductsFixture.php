<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Setup\Model\Complex\Generator;
use Magento\Setup\Model\Complex\Pattern;

/**
 * Class BundleProductsFixture
 */
class BundleProductsFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 42;

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
            'bundle_variation',
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
            'bundle_values',
        ];
    }

    private function generateBundleProduct($productCategory, $productWebsite, $variation, $suffix)
    {
        return [
            'sku' => 'Bundle Product %s' . $suffix,
            'store_view_code' => '',
            'attribute_set_code' => 'Default',
            'product_type' => 'bundle',
            'categories' => $productCategory,
            'product_websites' => $productWebsite,
            'color' => '',
            'bundle_variation' => '',
            'cost' => '',
            'country_of_manufacture' => '',
            'created_at' => '2013-10-25 15:12:39',
            'custom_design' => '',
            'custom_design_from' => '',
            'custom_design_to' => '',
            'custom_layout_update' => '',
            'description' => '<p>Bundle product description %s</p>',
            'enable_googlecheckout' => '1',
            'gallery' => '',
            'gift_message_available' => '',
            'gift_wrapping_available' => '',
            'gift_wrapping_price' => '',
            'has_options' => '1',
            'image' => '',
            'image_label' => '',
            'is_returnable' => 'Use config',
            'manufacturer' => '',
            'meta_description' => 'Bundle Product %s <p>Bundle product description %s</p>',
            'meta_keyword' => 'Bundle Product %s',
            'meta_title' => 'Bundle Product %s',
            'minimal_price' => '',
            'msrp' => '',
            'msrp_display_actual_price_type' => 'Use config',
            'name' => 'Bundle Product %s' . $suffix,
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
            'url_key' => "bundle-product-%s{$suffix}",
            'url_path' => "bundle-product-%s{$suffix}",
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
            'bundle_price_type' => 'dynamic',
            'bundle_sku_type' => 'dynamic',
            'bundle_price_view' => 'Price range',
            'bundle_weight_type' => 'dynamic',
            'bundle_values'     => $variation,
            'bundle_shipment_type' => 'separately',
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
    protected function getRows($productCategory, $productWebsite, $optionsNumber, $suffix = '')
    {
        $data = [];
        $variation = [];
        for ($i = 1; $i <= $optionsNumber; $i++) {
            $productData = [
                'sku' => "Bundle Product %s-option {$i}{$suffix}",
                'store_view_code' => '',
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'categories' => $productCategory,
                'product_websites' => $productWebsite,
                'cost' => '',
                'country_of_manufacture' => '',
                'created_at' => '2013-10-25 15:12:32',
                'custom_design' => '',
                'custom_design_from' => '',
                'custom_design_to' => '',
                'custom_layout_update' => '',
                'description' => '<p>Bundle product option description %s</p>',
                'enable_googlecheckout' => '1',
                'gallery' => '',
                'gift_message_available' => '',
                'gift_wrapping_available' => '',
                'gift_wrapping_price' => '',
                'has_options' => '0',
                'image' => '',
                'image_label' => '',
                'is_returnable' => 'Use config',
                'manufacturer' => '',
                'meta_description' => 'Bundle Product Option %s <p>Bundle product description 1</p>',
                'meta_keyword' => 'Bundle Product 1',
                'meta_title' => 'Bundle Product %s',
                'minimal_price' => '',
                'msrp' => '',
                'msrp_display_actual_price_type' => 'Use config',
                'name' => "Bundle Product {$suffix} -  %s-option {$i}",
                'news_from_date' => '',
                'news_to_date' => '',
                'options_container' => 'Block after Info Column',
                'page_layout' => '',
                'price' => function () { return mt_rand(1, 1000) / 10; },
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
                'url_key' => "simple-of-bundle-product-{$suffix}-%s-option-{$i}",
                'url_path' => "simple-of-bundle-product-{$suffix}-%s-option-{$i}",
                'visibility' => 'Not Visible Individually',
                'weight' => '1',
                'qty' => '111.0000',
                'min_qty' => '0.0000',
                'use_config_min_qty' => '1',
                'use_config_backorders' => '1',
                'use_config_min_sale_qty' => '1',
                'use_config_max_sale_qty' => '1',
                'is_in_stock' => '1',
                'use_config_notify_stock_qty' => '1',
                'use_config_manage_stock' => '1',
                'use_config_qty_increments' => '1',
                'use_config_enable_qty_inc' => '1',
                'enable_qty_increments' => '0',
                'is_decimal_divided' => '0',
            ];
            $variation[] = implode(
                ',',
                [
                    'name=Bundle Option 1',
                    'type=select',
                    'required=1',
                    'sku=' . $productData['sku'],
                    'price=' . mt_rand(1, 1000) / 10,
                    'default=0',
                    'default_qty=1',
                ]
            );
            $data[] = $productData;
        }

        $data[] = $this->generateBundleProduct($productCategory, $productWebsite, implode('|', $variation), $suffix);
        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $bundlesCount = $this->fixtureModel->getValue('bundle_products', 0);
        if (!$bundlesCount) {
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
         * Create bundle products
         */
        $pattern = new Pattern();
        $pattern->setHeaders($this->getHeaders());
        $pattern->setRowsSet(
            $this->getRows(
                $productCategory,
                $productWebsite,
                $this->fixtureModel->getValue('bundle_products_variation', 5000)
            )
        );

        /** @var \Magento\ImportExport\Model\Import $import */
        $import = $this->fixtureModel->getObjectManager()->create(
            'Magento\ImportExport\Model\Import',
            [
                'data' => [
                    'entity' => 'catalog_product',
                    'behavior' => 'append',
                    'validation_strategy' => 'validation-stop-on-errors',
                ],
            ]
        );

        $source = new Generator($pattern, $bundlesCount);
        // it is not obvious, but the validateSource() will actually save import queue data to DB
        if (!$import->validateSource($source)) {
            throw new \Exception($import->getFormatedLogTrace());
        }
        // this converts import queue into actual entities
        if (!$import->importSource()) {
            throw new \Exception($import->getFormatedLogTrace());
        }
    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating bundle products';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'bundle_products' => 'Bundle products',
        ];
    }
}
