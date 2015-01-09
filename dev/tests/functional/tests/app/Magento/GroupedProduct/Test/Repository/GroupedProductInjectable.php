<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class GroupedProductInjectable
 * Data for creation Catalog Product Grouped
 */
class GroupedProductInjectable extends AbstractRepository
{
    /**
     * Constructor
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'name' => 'Test grouped product %isolation%',
            'sku' => 'sku_test_grouped_product_%isolation%',
            'category_ids' => ['presets' => 'default'],
            'associated' => ['preset' => 'defaultSimpleProduct'],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'test-grouped-product-%isolation%',
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
        ];

        $this->_data['grouped_product_out_of_stock'] = [
            'name' => 'Test grouped product %isolation%',
            'sku' => 'sku_test_grouped_product_%isolation%',
            'category_ids' => ['presets' => 'default'],
            'associated' => ['preset' => 'defaultSimpleProduct'],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'test-grouped-product-%isolation%',
            'quantity_and_stock_status' => [
                'is_in_stock' => 'Out of Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
        ];

        $this->_data['grouped_product_with_price'] = [
            'name' => 'Test grouped product %isolation%',
            'sku' => 'sku_test_grouped_product_%isolation%',
            'price' => ['value' => '-', 'preset' => 'starting-560'],
            'category_ids' => ['presets' => 'default'],
            'associated' => ['preset' => 'defaultSimpleProduct'],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'test-grouped-product-%isolation%',
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
        ];

        $this->_data['three_simple_products'] = [
            'name' => 'Grouped product %isolation%',
            'sku' => 'grouped_product_%isolation%',
            'category_ids' => ['presets' => 'default'],
            'associated' => ['preset' => 'three_simple_products'],
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'test-grouped-product-%isolation%',
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'checkout_data' => ['preset' => 'three_simple_products'],
        ];
    }
}
