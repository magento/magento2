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
 * This testcase uses total based calculation.
 */
$taxCalculationData['multi_tax_rule_two_row_calculate_subtotal_yes_row'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::XML_PATH_ALGORITHM => Calculation::CALC_ROW_BASE,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 9,
            SetupUtil::TAX_RATE_AUSTIN => 5,
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
                'price' => 10.05,
                'qty' => 1,
            ],
            [
                'sku' => 'simple2',
                'price' => 10.45,
                'qty' => 1,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 20.5,
            'base_subtotal' => 20.5,
            'subtotal_incl_tax' => 23.36,
            'base_subtotal_incl_tax' => 23.36,
            'tax_amount' => 2.86,
            'base_tax_amount' => 2.86,
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
            'grand_total' => 23.36,
            'base_grand_total' => 23.36,
            'applied_taxes' => [
                SetupUtil::TAX_RATE_TX => [
                    'percent' => 9,
                    'amount' => 1.84,
                    'base_amount' => 1.84,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_TX,
                            'title' => SetupUtil::TAX_RATE_TX,
                            'percent' => 9,
                        ],
                    ],
                ],
                SetupUtil::TAX_RATE_AUSTIN => [
                    'percent' => 5,
                    'amount' => 1.02,
                    'base_amount' => 1.02,
                    'rates' => [
                        [
                            'code' => SetupUtil::TAX_RATE_AUSTIN,
                            'title' => SetupUtil::TAX_RATE_AUSTIN,
                            'percent' => 5,
                        ],
                    ],
                ],
            ],
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 10.05,
                'base_row_total' => 10.05,
                'tax_percent' => 14,
                'price' => 10.05,
                'base_price' => 10.05,
                'price_incl_tax' => 11.45,
                'base_price_incl_tax' => 11.45,
                'row_total_incl_tax' => 11.45,
                'base_row_total_incl_tax' => 11.45,
                'tax_amount' => 1.4,
                'base_tax_amount' => 1.4,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
                'applied_taxes' => [
                    [
                        'amount' => 0.9,
                        'base_amount' => 0.9,
                        'percent' => 9,
                        'id' => SetupUtil::TAX_RATE_TX,
                        'rates' => [
                            [
                                'percent' => 9,
                                'code' => SetupUtil::TAX_RATE_TX,
                                'title' => SetupUtil::TAX_RATE_TX,
                            ],
                        ],
                        'item_id' => null,
                        'item_type' => 'product',
                        'associated_item_id' => null,
                    ],
                    [
                        'amount' => 0.5,
                        'base_amount' => 0.5,
                        'percent' => 5,
                        'id' => SetupUtil::TAX_RATE_AUSTIN,
                        'rates' => [
                            [
                                'percent' => 5,
                                'code' => SetupUtil::TAX_RATE_AUSTIN,
                                'title' => SetupUtil::TAX_RATE_AUSTIN,
                            ],
                        ],
                        'item_id' => null,
                        'item_type' => 'product',
                        'associated_item_id' => null,
                    ],
                ],
            ],
            'simple2' => [
                'row_total' => 10.45,
                'base_row_total' => 10.45,
                'tax_percent' => 14,
                'price' => 10.45,
                'base_price' => 10.45,
                'price_incl_tax' => 11.91,
                'base_price_incl_tax' => 11.91,
                'row_total_incl_tax' => 11.91,
                'base_row_total_incl_tax' => 11.91,
                'tax_amount' => 1.46,
                'base_tax_amount' => 1.46,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
                'applied_taxes' => [
                    [
                        'amount' => 0.94,
                        'base_amount' => 0.94,
                        'percent' => 9,
                        'id' => SetupUtil::TAX_RATE_TX,
                        'rates' => [
                            [
                                'percent' => 9,
                                'code' => SetupUtil::TAX_RATE_TX,
                                'title' => SetupUtil::TAX_RATE_TX,
                            ],
                        ],
                        'item_id' => null,
                        'item_type' => 'product',
                        'associated_item_id' => null,
                    ],
                    [
                        'amount' => 0.52,
                        'base_amount' => 0.52,
                        'percent' => 5,
                        'id' => SetupUtil::TAX_RATE_AUSTIN,
                        'rates' => [
                            [
                                'percent' => 5,
                                'code' => SetupUtil::TAX_RATE_AUSTIN,
                                'title' => SetupUtil::TAX_RATE_AUSTIN,
                            ],
                        ],
                        'item_id' => null,
                        'item_type' => 'product',
                        'associated_item_id' => null,
                    ],
                ],
            ],
        ],
    ],
];
