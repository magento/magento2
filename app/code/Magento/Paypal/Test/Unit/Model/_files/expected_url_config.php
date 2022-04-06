<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Generates expected PayPal SDK url
 * @param array $params
 * @return String
 */
function generateExpectedPaypalSdkUrl(array $params) : String
{
    return 'https://www.paypal.com/sdk/js?' . http_build_query($params);
}

return [
    'authorize' => [
        'es_MX',
        'Authorization',
        'CREDIT,ELV,CARD',
        false,
        true,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'es_MX',
                    'currency' => 'USD',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'authorize',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        [
                            'credit',
                            'sepa',
                            'card',
                            'bancontact',
                            'eps',
                            'giropay',
                            'ideal',
                            'mybank',
                            'p24',
                            'sofort'
                        ]
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
    'capture' => [
        'en_BR',
        'Sale',
        null,
        false,
        true,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'en_BR',
                    'currency' => 'USD',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'capture',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        ['bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
    'order' => [
        'en_US',
        'Order',
        null,
        false,
        true,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'en_US',
                    'currency' => 'USD',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'order',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        ['bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
    'paypal_guest_checkout_disabled' => [
        'en_BR',
        'Authorization',
        'CREDIT,ELV',
        false,
        false,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'en_BR',
                    'currency' => 'USD',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'authorize',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        [
                            'credit',
                            'sepa',
                            'card',
                            'bancontact',
                            'eps',
                            'giropay',
                            'ideal',
                            'mybank',
                            'p24',
                            'sofort'
                        ]
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
    'paypal_guest_checkout_enabled' => [
        'en_BR',
        'Authorization',
        'CREDIT,ELV',
        false,
        true,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'en_BR',
                    'currency' => 'USD',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'authorize',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        ['credit', 'sepa', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
    'buyer_country_enabled' => [
        'en_BR',
        'Authorization',
        'CREDIT,ELV',
        true,
        true,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'en_BR',
                    'currency' => 'USD',
                    'buyer-country' => 'US',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'authorize',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        ['credit', 'sepa', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
    'buyer_country_disabled' => [
        'en_BR',
        'Authorization',
        'CREDIT,ELV',
        false,
        true,
        [
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'locale' => 'en_BR',
                    'currency' => 'USD',
                    'enable-funding' => implode(',', ['venmo', 'paylater']),
                    'commit' => 'false',
                    'intent' => 'authorize',
                    'merchant-id' => 'merchant',
                    'disable-funding' => implode(
                        ',',
                        ['credit', 'sepa', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    ),
                    'components' => implode(',', ['messages', 'buttons']),
                ]
            )
        ]
    ],
];
