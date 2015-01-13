<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class ConfigurableProductInjectable
 * Data for creation Catalog Product Configurable
 */
class ConfigurableProductInjectable extends AbstractRepository
{
    /**
     * Constructor
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'name' => 'Test configurable product %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 120.00],
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'default'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'checkout_data' => ['preset' => 'default'],
        ];

        $this->_data['product_with_size'] = [
            'name' => 'Test configurable product with size %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 120.00],
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'size'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'checkout_data' => ['preset' => 'default'],
        ];

        $this->_data['product_with_color_and_size'] = [
            'name' => 'Test configurable product with color and size %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 120.00],
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'color_and_size'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'checkout_data' => ['preset' => 'default'],
        ];

        $this->_data['one_variation'] = [
            'name' => 'Test configurable product %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 120.00],
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'test-configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'one_variation'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
        ];

        $this->_data['not_virtual_for_type_switching'] = [
            'name' => 'Test configurable product %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 120.00],
            'is_virtual' => 'No',
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'default'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'checkout_data' => ['preset' => 'default'],
        ];

        $this->_data['with_one_option'] = [
            'name' => 'Test configurable product %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 10.00],
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'with_one_option'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
            'checkout_data' => ['preset' => 'with_one_option'],
        ];

        $this->_data['with_out_of_stock_item'] = [
            'name' => 'Test configurable product %isolation%',
            'sku' => 'sku_test_configurable_product_%isolation%',
            'price' => ['value' => 120.00],
            'weight' => 30.0000,
            'status' => 'Product online',
            'visibility' => 'Catalog, Search',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'url_key' => 'test-configurable-product-%isolation%',
            'configurable_attributes_data' => ['preset' => 'with_out_of_stock_item'],
            'quantity_and_stock_status' => [
                'is_in_stock' => 'In Stock',
            ],
            'website_ids' => ['Main Website'],
            'attribute_set_id' => ['dataSet' => 'default'],
        ];
    }
}
