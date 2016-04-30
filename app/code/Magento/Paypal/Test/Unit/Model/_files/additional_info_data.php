<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

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
            Info::PAYPAL_FRAUD_FILTERS => [Info::PAYPAL_FRAUD_FILTERS, Info::PAYPAL_FRAUD_FILTERS],
            Info::PAYPAL_CORRELATION_ID => Info::PAYPAL_CORRELATION_ID,
            Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
            Info::PAYPAL_AVS_CODE => 'A',
            Info::PAYPAL_CVV_2_MATCH => 'M',
            Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE_CNPJ,
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
                'value' => [
                    Info::PAYPAL_FRAUD_FILTERS,
                    Info::PAYPAL_FRAUD_FILTERS
                    ]
            ],
            Info::PAYPAL_CORRELATION_ID => [
                'label' => 'Last Correlation ID',
                'value' => Info::PAYPAL_CORRELATION_ID,
            ],
            Info::PAYPAL_AVS_CODE => [
                'label' => 'Address Verification System Response',
                'value' => '#A: Matched Address only (no ZIP)',
            ],
            Info::PAYPAL_CVV_2_MATCH => [
                'label' => 'CVV2 Check Result by PayPal',
                'value' => '#M: Matched (CVV2CSC)',
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
                'value' => NULL,
            ]
        ],
    ],
    [
        [
            Info::PAYPAL_PAYER_ID => Info::PAYPAL_PAYER_ID,
            Info::PAYPAL_PAYER_EMAIL => Info::PAYPAL_PAYER_EMAIL,
            Info::PAYPAL_PAYER_STATUS => Info::PAYPAL_PAYER_STATUS,
            Info::PAYPAL_ADDRESS_ID => Info::PAYPAL_ADDRESS_ID,
            Info::PAYPAL_ADDRESS_STATUS => Info::PAYPAL_ADDRESS_STATUS,
            Info::PAYPAL_PROTECTION_ELIGIBILITY => Info::PAYPAL_PROTECTION_ELIGIBILITY,
            Info::PAYPAL_FRAUD_FILTERS => [Info::PAYPAL_FRAUD_FILTERS, Info::PAYPAL_FRAUD_FILTERS],
            Info::PAYPAL_CORRELATION_ID => Info::PAYPAL_CORRELATION_ID,
            Info::BUYER_TAX_ID => Info::BUYER_TAX_ID,
            Info::PAYPAL_AVS_CODE => Info::PAYPAL_AVS_CODE,
            Info::PAYPAL_CVV_2_MATCH => Info::PAYPAL_CVV_2_MATCH,
            Info::BUYER_TAX_ID_TYPE => Info::BUYER_TAX_ID_TYPE,
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
                'value' => [
                    Info::PAYPAL_FRAUD_FILTERS,
                    Info::PAYPAL_FRAUD_FILTERS
                ],
            ],
            Info::PAYPAL_CORRELATION_ID => [
                'label' => 'Last Correlation ID',
                'value' => Info::PAYPAL_CORRELATION_ID,
            ],
            Info::PAYPAL_AVS_CODE => [
                'label' => 'Address Verification System Response',
                'value' => '#paypal_avs_code',
            ],
            Info::PAYPAL_CVV_2_MATCH => [
                'label' => 'CVV2 Check Result by PayPal',
                'value' => '#paypal_cvv_2_match',
            ],
            Info::BUYER_TAX_ID => [
                'label' => 'Buyer\'s Tax ID',
                'value' => Info::BUYER_TAX_ID,
            ],
            'last_trans_id' => [
                'label' => 'Last Transaction ID',
                'value' => NULL,
            ]
        ]
    ]
];
