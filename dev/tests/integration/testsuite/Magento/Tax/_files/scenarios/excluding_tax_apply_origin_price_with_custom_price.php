<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

$taxCalculationData['excluding_tax_apply_origin_price_with_custom_price'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_ON => 1,
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
                'price' => 16.24,
                'qty' => 1,
            ],
        ],
        'update_items' => [
            'simple1' => [
                'custom_price' => 14,
                'qty' => 1,
            ],
        ],
    ],
    'expected_results' => [
        'address_data' => [
            'subtotal' => 14,
            'base_subtotal' => 14,
            'subtotal_incl_tax' => 15.34,
            'base_subtotal_incl_tax' => 15.34,
            'tax_amount' => 1.34,
            'base_tax_amount' => 1.34,
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
            'grand_total' => 15.34,
            'base_grand_total' => 15.34,
        ],
        'items_data' => [
            'simple1' => [
                'row_total' => 14,
                'base_row_total' => 14,
                'tax_percent' => 8.25,
                'price' => 14,
                'custom_price' => 14,
                'original_custom_price' => 14,
                'base_price' => 14,
                'price_incl_tax' => 15.34,
                'base_price_incl_tax' => 15.34,
                'row_total_incl_tax' => 15.34,
                'base_row_total_incl_tax' => 15.34,
                'tax_amount' => 1.34,
                'base_tax_amount' => 1.34,
                'discount_amount' => 0,
                'base_discount_amount' => 0,
                'discount_percent' => 0,
                'discount_tax_compensation_amount' => 0,
                'base_discount_tax_compensation_amount' => 0,
            ],
        ],
    ],
];
