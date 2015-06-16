<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

$taxCalculationData['excluding_tax_apply_tax_after_discount'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS => SetupUtil::SHIPPING_TAX_CLASS,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 20,
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
                'price' => 10,
                'qty' => 2,
            ],
        ],
        'shipping_method' => 'flatrate_flatrate',
        'shopping_cart_rules' => [
            [
                'discount_amount' => 50,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 20,
            'base_subtotal' => 20,
            'subtotal_incl_tax' => 24,
            'base_subtotal_incl_tax' => 24,
            'tax_amount' => 2.75,
            'base_tax_amount' => 2.75,
            'shipping_amount' => 10,
            'base_shipping_amount' => 10,
            'shipping_incl_tax' => 10.75,
            'base_shipping_incl_tax' => 10.75,
            'shipping_tax_amount' => 0.75,
            'base_shipping_tax_amount' => 0.75,
            'discount_amount' => -10,
            'base_discount_amount' => -10,
            'discount_tax_compensation_amount' => 0,
            'base_discount_tax_compensation_amount' => 0,
            'shipping_discount_tax_compensation_amount' => 0,
            'base_shipping_discount_tax_compensation_amount' => 0,
            'grand_total' => 22.75,
            'base_grand_total' => 22.75,
            'applied_taxes' => [
                SetupUtil::TAX_RATE_TX => [
                    'percent' => 20,
                    'amount' => 2,
                    'base_amount' => 2,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_TX,
                            'title' => SetupUtil::TAX_RATE_TX,
                            'percent' => 20,
                        ],
                    ],
                ],
                SetupUtil::TAX_RATE_SHIPPING => [
                    'percent' => 7.5,
                    'amount' => 0.75,
                    'base_amount' => 0.75,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_SHIPPING,
                            'title' => SetupUtil::TAX_RATE_SHIPPING,
                            'percent' => 7.5,
                        ],
                    ],
                ],
            ],
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 20,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 12,
                'base_price_incl_tax' => 12,
                'row_total_incl_tax' => 24,
                'base_row_total_incl_tax' => 24,
                'tax_amount' => 2,
                'base_tax_amount' => 2,
                'discount_amount' => 10,
                'base_discount_amount' => 10,
                'discount_percent' => 50,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
        ],
    ],
];
