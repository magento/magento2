<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

$taxCalculationData['excluding_tax_row'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 1,
            Config::XML_PATH_ALGORITHM => Calculation::CALC_ROW_BASE,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 8.25,
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
                'price' => 10,
                'qty' => 2,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 20,
            'base_subtotal' => 20,
            'subtotal_incl_tax' => 21.65,
            'base_subtotal_incl_tax' => 21.65,
            'tax_amount' => 1.65,
            'base_tax_amount' => 1.65,
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
            'grand_total' => 21.65,
            'base_grand_total' => 21.65,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 20,
                'base_row_total' => 20,
                'tax_percent' => 8.25,
                'price' => 10,
                'base_price' => 10,
                'price_incl_tax' => 10.83,
                'base_price_incl_tax' => 10.83,
                'row_total_incl_tax' => 21.65,
                'base_row_total_incl_tax' => 21.65,
                'tax_amount' => 1.65,
                'base_tax_amount' => 1.65,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
        ],
    ],
];
