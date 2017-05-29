<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

$taxCalculationData['excluding_tax__multi_item_unit'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX => 0,
            Config::XML_PATH_ALGORITHM => Calculation::CALC_UNIT_BASE,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 8.25,
            SetupUtil::TAX_STORE_RATE => 8.25,
        ],
        SetupUtil::TAX_RULE_OVERRIDES => [
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
                'price' => 10.00,
                'qty' => 1,
            ],
            [
                'sku' => 'simple2',
                'price' => 11.00,
                'qty' => 1,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 21,
            'base_subtotal' => 21,
            'subtotal_incl_tax' => 22.74,
            'base_subtotal_incl_tax' => 22.74,
            'tax_amount' => 1.74,
            'base_tax_amount' => 1.74,
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
            'grand_total' => 22.74,
            'base_grand_total' => 22.74,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 10,
                'base_row_total' => 10,
                'tax_percent' => 8.25,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10.83,
                'base_price_incl_tax' => 10.83,
                'row_total_incl_tax' => 10.83,
                'base_row_total_incl_tax' => 10.83,
                'tax_amount' => 0.83,
                'base_tax_amount' => 0.83,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
            'simple2' => [
                'row_total' => 11,
                'base_row_total' => 11,
                'tax_percent' => 8.25,
                'price' => 11,
                'base_price' => 11,
                'price_incl_tax' => 11.91,
                'base_price_incl_tax' => 11.91,
                'row_total_incl_tax' => 11.91,
                'base_row_total_incl_tax' => 11.91,
                'tax_amount' => 0.91,
                'base_tax_amount' => 0.91,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
        ],
    ],
];
