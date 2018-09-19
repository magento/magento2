<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    [
        'empty structure',
        []
    ],
    [
        'structure with special groups at the begin of the list',
        [
            'account' => [
                'id' => 'account',
            ],
            'recommended_solutions' => [
                'id' => 'recommended_solutions',
            ],
            'other_paypal_payment_solutions' => [
                'id' => 'other_paypal_payment_solutions',
            ],
            'other_payment_methods' => [
                'id' => 'other_payment_methods',
            ],
            'some_payment_method' => [
                'id' => 'some_payment_method',
            ],
        ]
    ],
    [
        'structure with special groups at the end of the list',
        [
            'some_payment_method' => [
                'id' => 'some_payment_method',
            ],
            'account' => [
                'id' => 'account',
            ],
            'recommended_solutions' => [
                'id' => 'recommended_solutions',
            ],
            'other_paypal_payment_solutions' => [
                'id' => 'other_paypal_payment_solutions',
            ],
            'other_payment_methods' => [
                'id' => 'other_payment_methods',
            ],
        ]
    ],
    [
        'structure with special groups in the middle of the list',
        [
            'some_payment_methodq' => [
                'id' => 'some_payment_methodq',
            ],
            'account' => [
                'id' => 'account',
            ],
            'recommended_solutions' => [
                'id' => 'recommended_solutions',
            ],
            'other_paypal_payment_solutions' => [
                'id' => 'other_paypal_payment_solutions',
            ],
            'other_payment_methods' => [
                'id' => 'other_payment_methods',
            ],
            'some_payment_method2' => [
                'id' => 'some_payment_method2',
            ],
        ]
    ],
    [
        'structure with all assigned groups',
        [
            'some_payment_method1' => [
                'id' => 'some_payment_method1',
                'displayIn' => 'other_paypal_payment_solutions',
            ],
            'some_payment_method2' => [
                'id' => 'some_payment_method2',
                'displayIn' => 'recommended_solutions',
            ],
        ]
    ],
    [
        'structure with not assigned groups',
        [
            'some_payment_method1' => [
                'id' => 'some_payment_method1',
                'displayIn' => 'other_paypal_payment_solutions',
            ],
            'some_payment_method2' => [
                'id' => 'some_payment_method2',
            ],
        ]
    ],
    [
        'special groups has predefined children',
        [
            'recommended_solutions' => [
                'id' => 'recommended_solutions',
                'children' => [
                    'some_payment_method1' => [
                        'id' => 'some_payment_method1',
                    ],
                ]
            ],
            'some_payment_method2' => [
                'id' => 'some_payment_method2',
                'displayIn' => 'recommended_solutions',
            ],
        ]
    ],
    [
        'structure with displayIn that do not reference to special groups',
        [
            'some_payment_method1' => [
                'id' => 'some_payment_method1',
            ],
            'some_payment_method2' => [
                'id' => 'some_payment_method2',
                'displayIn' => 'some_payment_method1',
            ],
        ]
    ],
];
