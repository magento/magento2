<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class BundleProduct
 * Data for creation Catalog Product Bundle
 */
class BundleProduct extends AbstractRepository
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
        $this->_data['BundleDynamic_sku_1073507449'] = [
            'sku' => 'BundleDynamic_sku_10735074493',
            'name' => 'BundleDynamic 1073507449',
            'price' => [
                'price_from' => 1,
                'price_to' => 2,
            ],
            'short_description' => '',
            'description' => '',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'sku_type' => '0',
            'price_type' => '0',
            'weight_type' => '0',
            'status' => 'Product online',
            'shipment_type' => '1',
            'mtf_dataset_name' => 'BundleDynamic_sku_1073507449',
            'website_ids' => ['Main Website'],
        ];

        $this->_data['BundleDynamic_sku_215249172'] = [
            'sku' => 'BundleDynamic_sku_215249172',
            'name' => 'BundleDynamic 215249172',
            'price' => [
                'price_from' => 3,
                'price_to' => 4,
            ],
            'short_description' => '',
            'description' => '',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'sku_type' => '0',
            'weight_type' => '0',
            'price_type' => '0',
            'shipment_type' => '1',
            'mtf_dataset_name' => 'BundleDynamic_sku_215249172',
            'website_ids' => ['Main Website'],
        ];

        $this->_data['bundle_dynamic_product'] = [
            'name' => 'Bundle dynamic product %isolation%',
            'sku' => 'sku_bundle_dynamic_product_%isolation%',
            'sku_type' => 'Dynamic',
            'price_type' => 'Dynamic',
            'price' => ['value' => '-', 'preset' => 'default_dynamic'],
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight_type' => 'Dynamic',
            'shipment_type' => 'Separately',
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'website_ids' => ['Main Website'],
            'stock_data' => [
                'manage_stock' => 'Yes',
                'use_config_enable_qty_increments' => 'Yes',
                'use_config_qty_increments' => 'Yes',
                'is_in_stock' => 'In Stock',
            ],
            'url_key' => 'bundle-dynamic-product-%isolation%',
            'visibility' => 'Catalog, Search',
            'bundle_selections' => ['preset' => 'default_dynamic'],
            'attribute_set_id' => 'Default',
            'checkout_data' => ['preset' => 'default_dynamic'],
        ];

        $this->_data['bundle_fixed_product'] = [
            'name' => 'Bundle fixed product %isolation%',
            'sku' => 'sku_bundle_fixed_product_%isolation%',
            'sku_type' => 'Fixed',
            'price_type' => 'Fixed',
            'price' => ['value' => 750.00, 'preset' => 'default_fixed'],
            'tax_class_id' => ['dataSet' => 'Taxable Goods'],
            'quantity_and_stock_status' => [
                'qty' => 666.0000,
                'is_in_stock' => 'In Stock',
            ],
            'weight' => 1.0000,
            'weight_type' => 'Fixed',
            'status' => 'Product online',
            'shipment_type' => 'Together',
            'website_ids' => ['Main Website'],
            'stock_data' => [
                'manage_stock' => 'Yes',
                'use_config_enable_qty_increments' => 'Yes',
                'use_config_qty_increments' => 'Yes',
                'is_in_stock' => 'In Stock',
            ],
            'url_key' => 'bundle-fixed-product-%isolation%',
            'visibility' => 'Catalog, Search',
            'bundle_selections' => ['preset' => 'default_fixed'],
            'attribute_set_id' => 'Default',
            'checkout_data' => ['preset' => 'default_fixed'],
        ];
    }
}
