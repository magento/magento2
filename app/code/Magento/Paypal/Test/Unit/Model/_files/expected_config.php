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
    'cart' => [
        'cart',
        'es_MX',
        true,
        'CREDIT',
        'horizontal',
        'pill',
        'installment',
        'blue',
        'my_label',
        'mx',
        true,
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => null,
                'color' => 'blue',
                'shape' => 'pill',
                'label' => 'installment',
                'period' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'commit' => 'false',
                    'merchant-id' => 'merchant',
                    'locale' => 'es_MX',
                    'intent' => 'authorize',
                    'currency' => 'USD',
                    'disable-funding' => implode(
                        ',',
                        ['credit', 'venmo', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    )
                ]
            )
        ]
    ],
    'checkout' => [
        'cart',
        'en_BR',
        true,
        null,
        'horizontal',
        'pill',
        'installment',
        'blue',
        'my_label',
        'br',
        true,
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => null,
                'color' => 'blue',
                'shape' => 'pill',
                'label' => 'installment',
                'period' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'commit' => 'false',
                    'merchant-id' => 'merchant',
                    'locale' => 'en_BR',
                    'intent' => 'authorize',
                    'currency' => 'USD',
                    'disable-funding' => implode(
                        ',',
                        ['venmo', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    )
                ]
            )
        ]
    ],
    'mini_cart' => [
        'cart',
        'en_US',
        false,
        null,
        'horizontal',
        'pill',
        'installment',
        'blue',
        'my_label',
        'br',
        true,
        [
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal'
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'commit' => 'false',
                    'merchant-id' => 'merchant',
                    'locale' => 'en_US',
                    'intent' => 'authorize',
                    'currency' => 'USD',
                    'disable-funding' => implode(
                        ',',
                        ['venmo', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    )
                ]
            )
        ]
    ],
    'product' => [
        'cart',
        'en_US',
        false,
        'CREDIT',
        'horizontal',
        'pill',
        'installment',
        'blue',
        'my_label',
        'br',
        true,
        [
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal',
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'commit' => 'false',
                    'merchant-id' => 'merchant',
                    'locale' => 'en_US',
                    'intent' => 'authorize',
                    'currency' => 'USD',
                    'disable-funding' => implode(
                        ',',
                        ['credit','venmo', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    )
                ]
            )
        ]
    ],
    'checkout_with_paypal_guest_checkout_disabled' => [
        'cart',
        'en_BR',
        true,
        null,
        'horizontal',
        'pill',
        'installment',
        'blue',
        'my_label',
        'br',
        false,
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => null,
                'color' => 'blue',
                'shape' => 'pill',
                'label' => 'installment',
                'period' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => generateExpectedPaypalSdkUrl(
                [
                    'client-id' => 'sb',
                    'commit' => 'false',
                    'merchant-id' => 'merchant',
                    'locale' => 'en_BR',
                    'intent' => 'authorize',
                    'currency' => 'USD',
                    'disable-funding' => implode(
                        ',',
                        ['card','venmo', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort']
                    )
                ]
            )
        ]
    ],
];
