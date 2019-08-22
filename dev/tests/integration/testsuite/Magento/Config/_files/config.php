<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'system' => [
        'default' => [
            'web' => [
                'test' => [
                    'test_value_2' => 'value2.local_config.default.test',
                ],
                'test2' => [
                    'test_value_3' => 'value3.local_config.default.test',
                    'test_value_4' => 'value4.local_config.default.test',
                ],
            ],
        ],
        'websites' => [
            'base' => [
                'web' => [
                    'test' => [
                        'test_value_2' => 'value2.local_config.website_base.test',
                    ],
                    'test2' => [
                        'test_value_3' => 'value3.local_config.website_base.test',
                        'test_value_4' => 'value4.local_config.website_base.test',
                    ],
                ],
            ],
        ],
        'stores' => [
            'default' => [
                'web' => [
                    'test' => [
                        'test_value_2' => 'value2.local_config.store_default.test',
                    ],
                    'test2' => [
                        'test_value_3' => 'value3.local_config.store_default.test',
                        'test_value_4' => 'value4.local_config.store_default.test',
                    ],
                ],
            ],
        ],
    ]
];
