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
            'SecondWebsite' => [
                'web' => [
                    'test' => [
                        'value' => 'configphp_test_value_website_2',
                    ]
                ]
            ],
            'THIRD_WEBSITE' => [
                'web' => [
                    'test' => [
                        'value' => 'configphp_test_value_website_3',
                    ]
                ]
            ],
            'fourthWebsite' => [
                'web' => [
                    'test' => [
                        'value' => 'configphp_test_value_website_4',
                    ]
                ]
            ]
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
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => 'test_value'
                    ]
                ]
            ],
            'SecondStore' => [
                'web' => [
                    'test' => [
                        'value' => 'configphp_test_value_store_2',
                    ]
                ]
            ],
            'THIRD_STORE' => [
                'web' => [
                    'test' => [
                        'value' => 'configphp_test_value_store_3',
                    ]
                ]
            ],
            'fourthStore' => [
                'web' => [
                    'test' => [
                        'value' => 'configphp_test_value_store_4',
                    ]
                ]
            ]
        ],
    ]
];
