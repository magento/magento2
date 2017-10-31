<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'setup_tests_table1' => [
        [
            'COLUMNS_LIST' => [
                'column_with_type_integer',
                'column_with_type_bigint',
            ],
            'INDEX_TYPE' => 'primary',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'primary',
            'fields' => [
                'column_with_type_integer',
                'column_with_type_bigint',
            ],
        ],
        [
            'COLUMNS_LIST' =>
                ['column_with_type_integer',],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' =>
                ['column_with_type_integer',]
        ],
        [
            'COLUMNS_LIST' =>
                ['column_with_type_text',],
            'INDEX_TYPE' => 'fulltext',
            'INDEX_METHOD' => 'FULLTEXT',
            'type' => 'fulltext',
            'fields' =>
                ['column_with_type_text'],
        ],
    ],
    'setup_tests_table1_related' => [
        [
            'COLUMNS_LIST' => [
                'column_with_relation',
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' =>
                ['column_with_relation'],
        ],
    ],
    'setup_tests_entity_table' => [
        [
            'COLUMNS_LIST' => [
                'entity_id',
            ],
            'INDEX_TYPE' => 'primary',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'primary',
            'fields' =>
                ['entity_id'],
        ],
        [
            'COLUMNS_LIST' => [
                'email_field',
                'website_id'
            ],
            'INDEX_TYPE' => 'unique',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'unique',
            'fields' =>
                [
                    'email_field',
                    'website_id'
                ],
        ],
        [
            'COLUMNS_LIST' => [
                'website_id'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' =>
                [
                    'website_id'
                ],
        ],
        [
            'COLUMNS_LIST' => [
                'firstname'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' =>
                [
                    'firstname'
                ],
        ],
        [
            'COLUMNS_LIST' => [
                'lastname'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' =>
                [
                    'lastname'
                ],
        ]
    ],
    'setup_tests_address_entity' => [
        [
            'COLUMNS_LIST' => [
                'entity_id'
            ],
            'INDEX_TYPE' => 'primary',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'primary',
            'fields' =>
                [
                    'entity_id'
                ],
        ],
        [
            'COLUMNS_LIST' => [
                'parent_id'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' => [
                    'parent_id'
                ],
        ]
    ],
    'setup_tests_address_entity_datetime' => [
        [
            'COLUMNS_LIST' => [
                'value_id'
            ],
            'INDEX_TYPE' => 'primary',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'primary',
            'fields' => [
                'value_id'
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'entity_id',
                'attribute_id'
            ],
            'INDEX_TYPE' => 'unique',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'unique',
            'fields' => [
                'entity_id',
                'attribute_id'
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'attribute_id'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' => [
                'attribute_id'
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'entity_id',
                'attribute_id',
                'value'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' => [
                'entity_id',
                'attribute_id',
                'value'
            ],
        ],
    ],
    'setup_tests_address_entity_decimal' => [
        [
            'COLUMNS_LIST' => [
                'value_id'
            ],
            'INDEX_TYPE' => 'primary',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'primary',
            'fields' => [
                'value_id'
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'entity_id',
                'attribute_id'
            ],
            'INDEX_TYPE' => 'unique',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'unique',
            'fields' => [
                'entity_id',
                'attribute_id'
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'attribute_id'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' => [
                'attribute_id'
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'entity_id',
                'attribute_id',
                'value'
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' => [
                'entity_id',
                'attribute_id',
                'value'
            ],
        ],
    ],
    'setup_tests_entity_passwords' => [
        [
            'COLUMNS_LIST' => [
                'password_id',
            ],
            'INDEX_TYPE' => 'primary',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'primary',
            'fields' => [
                'password_id',
            ],
        ],
        [
            'COLUMNS_LIST' => [
                'entity_id',
            ],
            'INDEX_TYPE' => 'index',
            'INDEX_METHOD' => 'BTREE',
            'type' => 'index',
            'fields' => [
                'entity_id',
            ],
        ],
    ]
];
