<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'entity_0' => [
        'data_0' => [
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
            'configurable_variations' => ''
        ],
    ],
    'entity_1' => [
        'data_0' => [
            'sku' => '%sku%',
            'attribute_set_code' => 'Default',
            'product_type' => "simple",
            'name' => '%name%',
            'product_websites' => 'base',
            'price' => '999.00',
            'url_key' => '%url_key%',
            'additional_attributes' => '',
            'weight' => '',
            'qty' => '999',
            'configurable_variations' => ''
        ],
    ],
    'entity_2' => [
        'data_0' => [
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
            'configurable_variations' => ''
        ],
        'data_1' => [
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
                "sku=%configurable_attribute_sku%,%configurable_additional_attributes%=black"
        ],
    ],
];
