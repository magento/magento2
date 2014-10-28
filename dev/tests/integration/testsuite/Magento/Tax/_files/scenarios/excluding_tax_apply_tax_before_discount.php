<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Total\Quote\SetupUtil;

$taxCalculationData['excluding_tax_apply_tax_before_discount'] = [
    'config_data' => [
        SetupUtil::CONFIG_OVERRIDES => [
            Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT => 0,
            Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS => SetupUtil::PRODUCT_TAX_CLASS_1,
        ],
        SetupUtil::TAX_RATE_OVERRIDES => [
            SetupUtil::TAX_RATE_TX => 20,
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
            'tax_amount' => 6,
            'base_tax_amount' => 6,
            'shipping_amount' => 10,
            'base_shipping_amount' => 10,
            'shipping_incl_tax' => 12,
            'base_shipping_incl_tax' => 12,
            'shipping_tax_amount' => 2,
            'base_shipping_tax_amount' => 2,
            'discount_amount' => -10,
            'base_discount_amount' => -10,
            'hidden_tax_amount' => 0,
            'base_hidden_tax_amount' => 0,
            'shipping_hidden_tax_amount' => 0,
            'base_shipping_hidden_tax_amount' => 0,
            'grand_total' => 26,
            'base_grand_total' => 26,
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
                'tax_amount' => 4,
                'base_tax_amount' => 4,
                'discount_amount' => 10,
                'base_discount_amount' => 10,
                'discount_percent' => 50,
                'hidden_tax_amount' => 0,
                'base_hidden_tax_amount' => 0,
            ],
        ],
    ],
];