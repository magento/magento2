<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

$taxCalculationData['including_tax_apply_tax_after_discount'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX => 1,
            Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX => 1,
            Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS => SetupUtil::SHIPPING_TAX_CLASS,
            Config::XML_PATH_ALGORITHM => Calculation::CALC_ROW_BASE,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 10,
            SetupUtil::TAX_STORE_RATE => 10,
            SetupUtil::TAX_RATE_SHIPPING => 10
        ],
        SetupUtil::TAX_RULE_OVERRIDES => [
            [
                //tax rule for product
                'code' => 'Product Tax Rule',
                'product_tax_class_ids' => [SetupUtil::PRODUCT_TAX_CLASS_1],
            ],
            [
                //tax rule for shipping
                'code' => 'Shipping Tax Rule',
                'product_tax_class_ids' => [SetupUtil::SHIPPING_TAX_CLASS],
                'tax_rate_ids' => [SetupUtil::TAX_RATE_SHIPPING],
            ],
        ],
    ],
    'quote_data' => [
        'billing_address' => [
            'region_id' => SetupUtil::REGION_TX,
        ],
        'shipping_address' => [
            'region_id' => SetupUtil::REGION_TX,
        ],
        'items' => [
            [
                'sku' => 'simple1',
                'price' => 29,
                'qty' => 1,
            ],
        ],
        'shipping_method' => 'flatrate_flatrate',
        'shopping_cart_rules' => [
            [
                'discount_amount' => 50,
                'apply_to_shipping' => 1,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 26.36,
            'base_subtotal' => 26.36,
            'subtotal_incl_tax' => 29,
            'base_subtotal_incl_tax' => 29,
            'tax_amount' => 1.69,
            'base_tax_amount' => 1.69,
            'shipping_amount' => 4.55,
            'base_shipping_amount' => 4.55,
            'shipping_incl_tax' => 5,
            'base_shipping_incl_tax' => 5,
            'shipping_tax_amount' => 0.25,
            'base_shipping_tax_amount' => 0.25,
            'discount_amount' => -15.455,
            'base_discount_amount' => -15.455,
            'discount_tax_compensation_amount' => 1.2,
            'base_discount_tax_compensation_amount' => 1.2,
            'shipping_discount_tax_compensation_amount' => 0.2,
            'base_shipping_discount_tax_compensation_amount' => 0.2,
            'grand_total' => 18.545,
            'base_grand_total' => 18.545,
            'applied_taxes' => [
                SetupUtil::TAX_RATE_TX => [
                    'percent' => 10,
                    'amount' => 1.44,
                    'base_amount' => 1.44,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_TX,
                            'title' => SetupUtil::TAX_RATE_TX,
                            'percent' => 10,
                        ],
                    ],
                ],
                SetupUtil::TAX_RATE_SHIPPING => [
                    'percent' => 10,
                    'amount' => 0.25,
                    'base_amount' => 0.25,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_SHIPPING,
                            'title' => SetupUtil::TAX_RATE_SHIPPING,
                            'percent' => 10,
                        ],
                    ],
                ],
            ],
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 26.36,
                'base_row_total' => 26.36,
                'tax_percent' => 10,
                'price' => 26.36,
                'base_price' => 26.36,
                'price_incl_tax' => 29,
                'base_price_incl_tax' => 29,
                'row_total_incl_tax' => 29,
                'base_row_total_incl_tax' => 29,
                'tax_amount' => 1.44,
                'base_tax_amount' => 1.44,
                'discount_amount' => 13.18,
                'base_discount_amount' => 13.18,
                'discount_percent' => 50,
                'discount_tax_compensation_amount' => 1.2,
                'base_discount_tax_compensation_amount' => 1.2,
            ],
        ],
    ],
];
