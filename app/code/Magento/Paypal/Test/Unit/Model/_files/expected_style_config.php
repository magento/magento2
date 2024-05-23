<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'cart' => [
        'cart',
        'es_MX',
        true,
        'horizontal',
        'pill',
        'installment',
        'blue',
        '5',
        'mx',
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => null,
                'color' => 'blue',
                'shape' => 'pill',
                'label' => 'installment',
                'period' => 5
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => 'http://mock.url',
            'dataAttributes' => [
                'data-partner-attribution-id' => '',
                'data-csp-nonce' => ''
            ]
        ]
    ],
    'checkout' => [
        'cart',
        'en_BR',
        true,
        'horizontal',
        'pill',
        'installment',
        'blue',
        '6',
        'br',
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => null,
                'color' => 'blue',
                'shape' => 'pill',
                'label' => 'installment',
                'period' => 6
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => 'http://mock.url',
            'dataAttributes' => [
                'data-partner-attribution-id' => '',
                'data-csp-nonce' => ''
            ]
        ]
    ],
    'mini_cart' => [
        'cart',
        'en_US',
        false,
        'horizontal',
        'pill',
        'installment',
        'blue',
        'no_value_expected',
        'br',
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
            'sdkUrl' => 'http://mock.url',
            'dataAttributes' => [
                'data-partner-attribution-id' => '',
                'data-csp-nonce' => ''
            ]
        ]
    ],
    'product' => [
        'cart',
        'en_US',
        false,
        'horizontal',
        'pill',
        'installment',
        'blue',
        'my_label',
        'br',
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
            'sdkUrl' => 'http://mock.url',
            'dataAttributes' => [
                'data-partner-attribution-id' => '',
                'data-csp-nonce' => ''
            ]
        ]
    ],
];
