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
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'es_MX',
            'allowedFunding' => ['ELV'],
            'disallowedFunding' => ['CREDIT'],
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true
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
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en_BR',
            'allowedFunding' => ['CREDIT', 'ELV'],
            'disallowedFunding' => [],
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true
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
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en',
            'allowedFunding' => ['CREDIT', 'ELV'],
            'disallowedFunding' => [],
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal'
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true
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
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en',
            'allowedFunding' => ['ELV'],
            'disallowedFunding' => ['CREDIT'],
            'styles' => [
                'layout' => 'vertical',
                'size' => 'responsive',
                'color' => 'gold',
                'shape' => 'rect',
                'label' => 'paypal',
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true
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
            'merchantId' => 'merchant',
            'environment' => 'sandbox',
            'locale' => 'en_BR',
            'allowedFunding' => ['CREDIT', 'ELV'],
            'disallowedFunding' => ['CARD'],
            'styles' => [
                'layout' => 'horizontal',
                'size' => 'small',
                'color' => 'blue',
                'shape' => 'pillow',
                'label' => 'installment',
                'installmentperiod' => 0
            ],
            'isVisibleOnProductPage' => false,
            'isGuestCheckoutAllowed' => true
        ]
    ],
];
