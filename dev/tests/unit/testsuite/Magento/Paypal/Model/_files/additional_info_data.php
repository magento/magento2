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
use Magento\Paypal\Model\Info;

return [
    [
        [
            Info::PAYPAL_PAYER_ID => Info::PAYPAL_PAYER_ID,
            Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
            Info::PAYPAL_PAYER_STATUS => Info::PAYPAL_PAYER_STATUS,
            Info::PAYPAL_ADDRESS_ID => Info::PAYPAL_ADDRESS_ID,
            Info::PAYPAL_ADDRESS_STATUS => Info::PAYPAL_ADDRESS_STATUS,
            Info::PAYPAL_PROTECTION_ELIGIBILITY => Info::PAYPAL_PROTECTION_ELIGIBILITY,
            Info::PAYPAL_FRAUD_FILTERS => Info::PAYPAL_FRAUD_FILTERS,
            Info::PAYPAL_CORRELATION_ID => Info::PAYPAL_CORRELATION_ID,
            Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
            Info::PAYPAL_AVS_CODE => 'A',
            Info::PAYPAL_CVV2_MATCH => 'M',
            Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE_CNPJ,
            Info::CENTINEL_VPAS => '2',
            Info::CENTINEL_ECI => '01'
        ],
        [
            Info::PAYPAL_PAYER_ID => [
                'label' => 'Payer ID',
                'value' => Info::PAYPAL_PAYER_ID,
            ],
            Info::PAYPAL_PAYER_EMAIL => [
                'label' => 'Payer Email',
                'value' => Info::PAYPAL_PAYER_EMAIL,
            ],
            Info::PAYPAL_PAYER_STATUS => [
                'label' => 'Payer Status',
                'value' => Info::PAYPAL_PAYER_STATUS,
            ],
            Info::PAYPAL_ADDRESS_ID => [
                'label' => 'Payer Address ID',
                'value' => Info::PAYPAL_ADDRESS_ID,
            ],
            Info::PAYPAL_ADDRESS_STATUS => [
                'label' => 'Payer Address Status',
                'value' => Info::PAYPAL_ADDRESS_STATUS,
            ],
            Info::PAYPAL_PROTECTION_ELIGIBILITY => [
                'label' => 'Merchant Protection Eligibility',
                'value' => Info::PAYPAL_PROTECTION_ELIGIBILITY,
            ],
            Info::PAYPAL_FRAUD_FILTERS => [
                'label' => 'Triggered Fraud Filters',
                'value' => Info::PAYPAL_FRAUD_FILTERS,
            ],
            Info::PAYPAL_CORRELATION_ID => [
                'label' => 'Last Correlation ID',
                'value' => Info::PAYPAL_CORRELATION_ID,
            ],
            Info::PAYPAL_AVS_CODE => [
                'label' => 'Address Verification System Response',
                'value' => '#A: Matched Address only (no ZIP)',
            ],
            Info::PAYPAL_CVV2_MATCH => [
                'label' => 'CVV2 Check Result by PayPal',
                'value' => '#M: Matched (CVV2CSC)',
            ],
            Info::CENTINEL_VPAS => [
                'label' => 'PayPal/Centinel Visa Payer Authentication Service Result',
                'value' => '#2: Authenticated, Good Result',
            ],
            Info::CENTINEL_ECI => [
                'label' => 'PayPal/Centinel Electronic Commerce Indicator',
                'value' => '#01: Merchant Liability',
            ],
            Info::BUYER_TAX_ID => [
                'label' => 'Buyer\'s Tax ID',
                'value' => Info::BUYER_TAX_ID,
            ],
            Info::BUYER_TAX_ID_TYPE => [
                'label' => 'Buyer\'s Tax ID Type',
                'value' => 'CNPJ',
            ],
            'last_trans_id' => [
                'label' => 'Last Transaction ID',
                'value' => NULL
            ]
        ]
    ],
    [
        [
            Info::PAYPAL_PAYER_ID => Info::PAYPAL_PAYER_ID,
            Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
            Info::PAYPAL_PAYER_STATUS => Info::PAYPAL_PAYER_STATUS,
            Info::PAYPAL_ADDRESS_ID => Info::PAYPAL_ADDRESS_ID,
            Info::PAYPAL_ADDRESS_STATUS => Info::PAYPAL_ADDRESS_STATUS,
            Info::PAYPAL_PROTECTION_ELIGIBILITY => Info::PAYPAL_PROTECTION_ELIGIBILITY,
            Info::PAYPAL_FRAUD_FILTERS => Info::PAYPAL_FRAUD_FILTERS,
            Info::PAYPAL_CORRELATION_ID => Info::PAYPAL_CORRELATION_ID,
            Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
            Info::PAYPAL_AVS_CODE => Info::PAYPAL_AVS_CODE,
            Info::PAYPAL_CVV2_MATCH => Info::PAYPAL_CVV2_MATCH,
            Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
            Info::CENTINEL_VPAS => Info::CENTINEL_VPAS,
            Info::CENTINEL_ECI => Info::CENTINEL_ECI
        ],
        [
            Info::PAYPAL_PAYER_ID => [
                'label' => 'Payer ID',
                'value' => Info::PAYPAL_PAYER_ID,
            ],
            Info::PAYPAL_PAYER_EMAIL => [
                'label' => 'Payer Email',
                'value' => Info::PAYPAL_PAYER_EMAIL,
            ],
            Info::PAYPAL_PAYER_STATUS => [
                'label' => 'Payer Status',
                'value' => Info::PAYPAL_PAYER_STATUS,
            ],
            Info::PAYPAL_ADDRESS_ID => [
                'label' => 'Payer Address ID',
                'value' => Info::PAYPAL_ADDRESS_ID,
            ],
            Info::PAYPAL_ADDRESS_STATUS => [
                'label' => 'Payer Address Status',
                'value' => Info::PAYPAL_ADDRESS_STATUS,
            ],
            Info::PAYPAL_PROTECTION_ELIGIBILITY => [
                'label' => 'Merchant Protection Eligibility',
                'value' => Info::PAYPAL_PROTECTION_ELIGIBILITY,
            ],
            Info::PAYPAL_FRAUD_FILTERS => [
                'label' => 'Triggered Fraud Filters',
                'value' => Info::PAYPAL_FRAUD_FILTERS,
            ],
            Info::PAYPAL_CORRELATION_ID => [
                'label' => 'Last Correlation ID',
                'value' => Info::PAYPAL_CORRELATION_ID,
            ],
            Info::PAYPAL_AVS_CODE => [
                'label' => 'Address Verification System Response',
                'value' => '#paypal_avs_code',
            ],
            Info::PAYPAL_CVV2_MATCH => [
                'label' => 'CVV2 Check Result by PayPal',
                'value' => '#paypal_cvv2_match',
            ],
            Info::CENTINEL_VPAS => [
                'label' => 'PayPal/Centinel Visa Payer Authentication Service Result',
                'value' => '#centinel_vpas_result',
            ],
            Info::CENTINEL_ECI => [
                'label' => 'PayPal/Centinel Electronic Commerce Indicator',
                'value' => '#centinel_eci_result',
            ],
            Info::BUYER_TAX_ID => [
                'label' => 'Buyer\'s Tax ID',
                'value' => Info::BUYER_TAX_ID,
            ],
            'last_trans_id' => [
                'label' => 'Last Transaction ID',
                'value' => NULL
            ]
        ]
    ]
];
