<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'setup_tests_table1' => [],
    'setup_tests_table1_related' => [
        'COLUMN_NAME' => 'column_with_relation',
        'REF_COLUMN_NAME' => 'column_with_type_integer',
        'ON_DELETE' => 'CASCADE',
    ],
    'setup_tests_entity_table' => [
        'COLUMN_NAME' => 'website_id',
        'REF_COLUMN_NAME' => 'website_id',
        'ON_DELETE' => 'SET NULL',
    ],
    'setup_tests_address_entity' => [
        'COLUMN_NAME' => 'parent_id',
        'REF_COLUMN_NAME' => 'entity_id',
        'ON_DELETE' => 'CASCADE',
    ],
    'setup_tests_address_entity_datetime' => [
        'COLUMN_NAME' => 'entity_id',
        'REF_COLUMN_NAME' => 'entity_id',
        'ON_DELETE' => 'CASCADE',
    ],
    'setup_tests_address_entity_decimal' => [
        'COLUMN_NAME' => 'entity_id',
        'REF_COLUMN_NAME' => 'entity_id',
        'ON_DELETE' => 'CASCADE',
    ],
    'setup_tests_entity_passwords' => [
        'COLUMN_NAME' => 'entity_id',
        'REF_COLUMN_NAME' => 'entity_id',
        'ON_DELETE' => 'CASCADE',
    ]
];
