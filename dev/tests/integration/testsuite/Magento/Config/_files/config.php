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
            ]
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
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => '',
                    ]
                ]
            ],
            'THIRD_WEBSITE' => [
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => 'local_config.website_third_website.test',
                    ]
                ]
            ],
            'fourthWebsite' => [
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => 'local_config.website_fourthwebsite.test',
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
                        'snake_case' => 'local_config.store_default.test'
                    ]
                ]
            ],
            'SecondStore' => [
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => 'local_config.store_secondstore.test',
                    ]
                ]
            ],
            'THIRD_STORE' => [
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => '',
                    ]
                ]
            ],
            'fourthStore' => [
                'camelCase' => [
                    'UPPERCASE' => [
                        'snake_case' => 'local_config.store_fourthstore.test',
                    ]
                ]
            ]
        ],
    ]
];
