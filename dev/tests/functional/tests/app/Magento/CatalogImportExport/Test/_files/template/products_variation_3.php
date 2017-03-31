<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'entity_0' =>
        [
            'data_0' =>
                [
                    'sku' => '%sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "simple",
                    'name' => '%name%',
                    'product_websites' => 'base,%code%',
                    'price' => '999.00',
                    'url_key' => '%url_key%',
                    'additional_attributes' => '',
                    'weight' => '',
                    'qty' => '999',
                    'configurable_variations' => '',
                    'associated_skus' => '',
                    'bundle_shipment_type' => '',
                    'bundle_values' => '',
                    'bundle_price_type' => '',
                    'bundle_price_view' => '',
                    'bundle_sku_type' => '',
                    'bundle_weight_type' => ''
                ],
        ],
    'entity_1' =>
        [
            'data_0' =>
                [
                    'sku' => '%configurable_attribute_sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "simple",
                    'name' => '%configurable_attribute_name%',
                    'product_websites' => 'base',
                    'price' => '50.00',
                    'url_key' => '%configurable_attribute_url_key%',
                    'additional_attributes' => '%configurable_additional_attributes%=black',
                    'weight' => '1',
                    'qty' => '50',
                    'configurable_variations' => '',
                    'associated_skus' => '',
                    'bundle_shipment_type' => '',
                    'bundle_values' => '',
                    'bundle_price_type' => '',
                    'bundle_price_view' => '',
                    'bundle_sku_type' => '',
                    'bundle_weight_type' => ''
                ],
            'data_1' =>
                [
                    'sku' => '%sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "configurable",
                    'name' => '%name%',
                    'product_websites' => 'base',
                    'price' => '50.00',
                    'url_key' => '%url_key%',
                    'additional_attributes' => '%configurable_additional_attributes%=black',
                    'weight' => '30',
                    'qty' => '50',
                    'configurable_variations' =>
                        "sku=%configurable_attribute_sku%,%configurable_additional_attributes%=black",
                    'associated_skus' => '',
                    'bundle_shipment_type' => '',
                    'bundle_values' => '',
                    'bundle_price_type' => '',
                    'bundle_price_view' => '',
                    'bundle_sku_type' => '',
                    'bundle_weight_type' => ''
                ],
        ],
    'entity_2' =>
        [
            'data_0' =>
                [
                    'sku' => '%grouped_attribute_sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "simple",
                    'name' => '%grouped_attribute_name%',
                    'product_websites' => 'base',
                    'price' => '50.00',
                    'url_key' => '%grouped_attribute_url_key%',
                    'additional_attributes' => '',
                    'weight' => '1',
                    'qty' => '50',
                    'configurable_variations' => '',
                    'associated_skus' => '',
                    'bundle_shipment_type' => '',
                    'bundle_values' => '',
                    'bundle_price_type' => '',
                    'bundle_price_view' => '',
                    'bundle_sku_type' => '',
                    'bundle_weight_type' => ''
                ],
            'data_1' =>
                [
                    'sku' => '%sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "grouped",
                    'name' => '%name%',
                    'product_websites' => 'base',
                    'price' => '50.00',
                    'url_key' => '%url_key%',
                    'additional_attributes' => '',
                    'weight' => '30',
                    'qty' => '50',
                    'configurable_variations' => "",
                    'associated_skus' => '%grouped_attribute_sku%=1.0000',
                    'bundle_shipment_type' => '',
                    'bundle_values' => '',
                    'bundle_price_type' => '',
                    'bundle_price_view' => '',
                    'bundle_sku_type' => '',
                    'bundle_weight_type' => ''
                ],
        ],
    'entity_3' =>
        [
            'data_0' =>
                [
                    'sku' => '%bundle_attribute_sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "simple",
                    'name' => '%bundle_attribute_name%',
                    'product_websites' => 'base',
                    'price' => '50.00',
                    'url_key' => '%bundle_attribute_url_key%',
                    'additional_attributes' => '',
                    'weight' => '1',
                    'qty' => '50',
                    'configurable_variations' => '',
                    'associated_skus' => '',
                    'bundle_shipment_type' => '',
                    'bundle_values' => '',
                    'bundle_price_type' => '',
                    'bundle_price_view' => '',
                    'bundle_sku_type' => '',
                    'bundle_weight_type' => ''
                ],
            'data_1' =>
                [
                    'sku' => '%sku%',
                    'attribute_set_code' => 'Default',
                    'product_type' => "bundle",
                    'name' => '%name%',
                    'product_websites' => 'base',
                    'price' => '50.00',
                    'url_key' => '%url_key%',
                    'additional_attributes' => '',
                    'weight' => '30',
                    'qty' => '50',
                    'configurable_variations' => "",
                    'associated_skus' => '%bundle_attribute_sku%',
                    'bundle_shipment_type' => 'together',
                    'bundle_values' => "name=Drop-down Option,type=select,required=1,sku=%bundle_attribute_sku%,"
                        . "price=0.0000,default=0,default_qty=1.0000,price_type=dynamic",
                    'bundle_price_type' => 'dynamic',
                    'bundle_price_view' => 'Price range',
                    'bundle_sku_type' => 'dynamic',
                    'bundle_weight_type' => 'fixed'
                ],
        ],
];
