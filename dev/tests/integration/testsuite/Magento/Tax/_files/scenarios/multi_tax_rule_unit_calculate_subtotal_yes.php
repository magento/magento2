<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

/**
 * This test case test the scenario where there are two tax rules with different priority
 * The calculate_subtotal field is on, the second tax rate will be applied on subtotal only.
 * This testcase uses unit based calculation.
 */
$taxCalculationData['multi_tax_rule_unit_calculate_subtotal_yes'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::XML_PATH_ALGORITHM => Calculation::CALC_UNIT_BASE,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 7.5,
            SetupUtil::TAX_RATE_AUSTIN => 5.5,
        ],
        SetupUtil::TAX_RULE_OVERRIDES => [
            [
                //tax rule 1 for product
                'code' => 'Product Tax Rule TX',
                'product_tax_class_ids' => [SetupUtil::PRODUCT_TAX_CLASS_1],
                'tax_rate_ids' => [SetupUtil::TAX_RATE_TX],
                'priority' => 1,
            ],
            [
                //tax rule 2 for product
                'code' => 'Product Tax Rule AUSTIN',
                'product_tax_class_ids' => [SetupUtil::PRODUCT_TAX_CLASS_1],
                'tax_rate_ids' => [SetupUtil::TAX_RATE_AUSTIN],
                'priority' => 2,
                'calculate_subtotal' => 1,
            ],
        ],
    ],
    'quote_data' => [
        'billing_address' => [
            'region_id' => SetupUtil::REGION_TX,
        ],
        'shipping_address' => [
            'region_id' => SetupUtil::REGION_TX,
            'tax_postcode' => SetupUtil::AUSTIN_POST_CODE,
        ],
        'items' => [
            [
                'sku' => 'simple1',
                'price' => 1,
                'qty' => 10,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 10,
            'base_subtotal' => 10,
            'subtotal_incl_tax' => 11.4,
            'base_subtotal_incl_tax' => 11.4,
            'tax_amount' => 1.4,
            'base_tax_amount' => 1.4,
            'shipping_amount' => 0,
            'base_shipping_amount' => 0,
            'shipping_incl_tax' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_taxable' => 0,
            'base_shipping_taxable' => 0,
            'shipping_tax_amount' => 0,
            'base_shipping_tax_amount' => 0,
            'discount_amount' => 0,
            'base_discount_amount' => 0,
            'discount_tax_compensation_amount' => 0,
            'base_discount_tax_compensation_amount' => 0,
            'shipping_discount_tax_compensation_amount' => 0,
            'base_shipping_discount_tax_compensation_amount' => 0,
            'grand_total' => 11.4,
            'base_grand_total' => 11.4,
            'applied_taxes' => [
                SetupUtil::TAX_RATE_TX => [
                    'percent' => 7.5,
                    'amount' => 0.8,
                    'base_amount' => 0.8,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_TX,
                            'title' => SetupUtil::TAX_RATE_TX,
                            'percent' => 7.5,
                        ],
                    ],
                ],
                SetupUtil::TAX_RATE_AUSTIN => [
                    'percent' => 5.5,
                    'amount' => 0.6,
                    'base_amount' => 0.6,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_AUSTIN,
                            'title' => SetupUtil::TAX_RATE_AUSTIN,
                            'percent' => 5.5,
                        ],
                    ],
                ],
            ],
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 10,
                'base_row_total' => 10,
                'tax_percent' => 13,
                'price' => 1,
                'base_price' => 1,
                'price_incl_tax' => 1.14,
                'base_price_incl_tax' => 1.14,
                'row_total_incl_tax' => 11.4,
                'base_row_total_incl_tax' => 11.4,
                'tax_amount' => 1.4,
                'base_tax_amount' => 1.4,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
        ],
    ],
];
