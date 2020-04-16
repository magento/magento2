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
        'CREDIT',
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'mx',
        true,
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => 'https://www.paypal.com/sdk/js?commit=false&merchant-id=merchant&locale=es_MX&' .
                'intent=authorize&disable-funding=CREDIT'
        ]
    ],
    'checkout' => [
        'cart',
        'en_BR',
        true,
        null,
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'br',
        true,
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => 'https://www.paypal.com/sdk/js?commit=false&merchant-id=merchant&locale=en_BR&intent=authorize'
        ]
    ],
    'mini_cart' => [
        'cart',
        'en',
        false,
        null,
        'horizontal',
        'small',
        'pillow',
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
            'sdkUrl' => 'https://www.paypal.com/sdk/js?commit=false&merchant-id=merchant&locale=en&intent=authorize'
        ]
    ],
    'product' => [
        'cart',
        'en',
        false,
        'CREDIT',
        'horizontal',
        'small',
        'pillow',
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
            'sdkUrl' => 'https://www.paypal.com/sdk/js?commit=false&merchant-id=merchant&locale=en&intent=authorize'
                . '&disable-funding=CREDIT'
        ]
    ],
    'checkout_with_paypal_guest_checkout_disabled' => [
        'cart',
        'en_BR',
        true,
        null,
        'horizontal',
        'small',
        'pillow',
        'installment',
        'blue',
        'my_label',
        'br',
        false,
        [
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true,
            'sdkUrl' => 'https://www.paypal.com/sdk/js?commit=false&merchant-id=merchant&locale=en_BR'
                . '&intent=authorize&disable-funding=CARD'
        ]
    ],
];
