<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

global $fullDiscountIncTax;
$fullDiscountIncTax = [
        'config_data' => [
                'config_overrides' => [
                        Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 0,
                        Config::CONFIG_XML_PATH_DISCOUNT_TAX => 1,
                        Config::XML_PATH_ALGORITHM => 'ROW_BASE_CALCULATION',
                        Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS => SetupUtil::SHIPPING_TAX_CLASS,
                    ],
                'tax_rate_overrides' => [
                        SetupUtil::TAX_RATE_TX => 18,
                        SetupUtil::TAX_RATE_SHIPPING => 0,
                    ],
                'tax_rule_overrides' => [
                        [
                            'code' => 'Product Tax Rule',
                            'product_tax_class_ids' => [
                                    SetupUtil::PRODUCT_TAX_CLASS_1
                                ],
                        ],
                        [
                            'code' => 'Shipping Tax Rule',
                            'product_tax_class_ids' => [
                                    SetupUtil::SHIPPING_TAX_CLASS
                                ],
                            'tax_rate_ids' => [
                                    SetupUtil::TAX_RATE_SHIPPING,
                                ],
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
                            'price' => 2542.37,
                            'qty' => 2,
                        ]
                    ],
                'shipping_method' => 'free',
                'shopping_cart_rules' => [
                        [
                            'discount_amount' => 100
                        ],
                    ],
        ],
        'expected_result' => [
                'address_data' => [
                        'subtotal' => 5084.74,
                        'base_subtotal' => 5084.74,
                        'subtotal_incl_tax' => 5999.99,
                        'base_subtotal_incl_tax' => 5999.99,
                        'tax_amount' => 915.25,
                        'base_tax_amount' => 915.25,
                        'shipping_amount' => 0,
                        'base_shipping_amount' => 0,
                        'shipping_incl_tax' => 0,
                        'base_shipping_incl_tax' => 0,
                        'shipping_tax_amount' => 0,
                        'base_shipping_tax_amount' => 0,
                        'discount_amount' => -5999.99,
                        'base_discount_amount' => -5999.99,
                        'discount_tax_compensation_amount' => 0,
                        'base_discount_tax_compensation_amount' => 0,
                        'shipping_discount_tax_compensation_amount' => 0,
                        'base_shipping_discount_tax_compensation_amount' => 0,
                        'grand_total' => 0,
                        'base_grand_total' => 0,
                        'applied_taxes' => [
                                SetupUtil::TAX_RATE_TX => [
                                        'percent' => 18,
                                        'amount' => 915.25,
                                        'base_amount' => 915.25,
                                        'rates' => [
                                                [
                                                    'code' => SetupUtil::TAX_RATE_TX,
                                                    'title' => SetupUtil::TAX_RATE_TX,
                                                    'percent' => 18,
                                                ],
                                            ],
                                    ]
                            ],
                    ],
                'items_data' => [
                        'simple1' => [
                                'row_total' => 5084.74,
                                'base_row_total' => 5084.74,
                                'tax_percent' => 18,
                                'price' => 2542.37,
                                'base_price' => 2542.37,
                                'price_incl_tax' => 3000,
                                'base_price_incl_tax' => 3000,
                                'row_total_incl_tax' => 5999.99,
                                'base_row_total_incl_tax' => 5999.99,
                                'tax_amount' => 915.25,
                                'base_tax_amount' => 915.25,
                                'discount_amount' => 5999.99,
                                'base_discount_amount' => 5999.99,
                                'discount_percent' => 100,
                                'discount_tax_compensation_amount' => 0,
                                'base_discount_tax_compensation_amount' => 0,
                            ],
                    ],
        ]
];
