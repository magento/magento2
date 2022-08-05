<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'table' => [
        'reference_table' => [
            'column' => [
                'tinyint_ref' => [
                    'type' => 'tinyint',
                    'name' => 'tinyint_ref',
                    'padding' => '7',
                    'nullable' => 'false',
                    'identity' => 'true',
                    'unsigned' => 'false',
                ],
                'tinyint_without_padding' => [
                    'type' => 'tinyint',
                    'name' => 'tinyint_without_padding',
                    'default' => '0',
                    'nullable' => 'false',
                    'unsigned' => 'false',
                ],
                'bigint_without_padding' => [
                    'type' => 'bigint',
                    'name' => 'bigint_without_padding',
                    'default' => '0',
                    'nullable' => 'false',
                    'unsigned' => 'false',
                ],
                'smallint_without_padding' => [
                    'type' => 'smallint',
                    'name' => 'smallint_without_padding',
                    'default' => '0',
                    'nullable' => 'false',
                    'unsigned' => 'false',
                ],
                'integer_without_padding' => [
                    'type' => 'int',
                    'name' => 'integer_without_padding',
                    'default' => '0',
                    'nullable' => 'false',
                    'unsigned' => 'false',
                ],
                'smallint_with_big_padding' => [
                    'type' => 'smallint',
                    'name' => 'smallint_with_big_padding',
                    'padding' => '254',
                    'default' => '0',
                    'nullable' => 'false',
                    'unsigned' => 'false',
                ],
                'smallint_without_default' => [
                    'type' => 'smallint',
                    'name' => 'smallint_without_default',
                    'padding' => '2',
                    'nullable' => 'true',
                    'unsigned' => 'false',
                ],
                'int_without_unsigned' => [
                    'type' => 'int',
                    'name' => 'int_without_unsigned',
                    'padding' => '2',
                    'nullable' => 'true',
                ],
                'int_unsigned' => [
                    'type' => 'int',
                    'name' => 'int_unsigned',
                    'padding' => '2',
                    'nullable' => 'true',
                    'unsigned' => 'true',
                ],
                'bigint_default_nullable' => [
                    'type' => 'bigint',
                    'name' => 'bigint_default_nullable',
                    'padding' => '2',
                    'nullable' => 'true',
                    'default' => '1',
                    'unsigned' => 'true',
                ],
                'bigint_not_default_not_nullable' => [
                    'type' => 'bigint',
                    'name' => 'bigint_not_default_not_nullable',
                    'padding' => '2',
                    'nullable' => 'false',
                    'unsigned' => 'true',
                ],
            ],
            'constraint' => [
                'tinyint_primary' => [
                    'column' => [
                        'tinyint_ref' => 'tinyint_ref',
                    ],
                    'type' => 'primary',
                    'referenceId' => 'tinyint_primary',
                ],
            ],
            'name' => 'reference_table',
            'resource' => 'default',
        ],
        'auto_increment_test' => [
            'column' => [
                'int_auto_increment_with_nullable' => [
                    'type' => 'int',
                    'name' => 'int_auto_increment_with_nullable',
                    'identity' => 'true',
                    'padding' => '12',
                    'unsigned' => 'true',
                    'nullable' => 'true',
                ],
                'int_disabled_auto_increment' => [
                    'type' => 'smallint',
                    'name' => 'int_disabled_auto_increment',
                    'default' => '0',
                    'identity' => 'false',
                    'padding' => '12',
                    'unsigned' => 'true',
                    'nullable' => 'true',
                ],
            ],
            'constraint' => [
                'AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE' => [
                    'column' => [
                        'int_auto_increment_with_nullable' => 'int_auto_increment_with_nullable',
                    ],
                    'type' => 'unique',
                    'referenceId' => 'AUTO_INCREMENT_TEST_INT_AUTO_INCREMENT_WITH_NULLABLE',
                ],
            ],
            'name' => 'auto_increment_test',
            'resource' => 'default',
        ],
        'test_table' => [
            'column' => [
                'smallint' => [
                    'type' => 'smallint',
                    'identity' => 'true',
                    'name' => 'smallint',
                    'padding' => '3',
                    'nullable' => 'true',
                ],
                'tinyint' => [
                    'type' => 'tinyint',
                    'name' => 'tinyint',
                    'padding' => '7',
                    'nullable' => 'true',
                    'unsigned' => 'false',
                ],
                'bigint' => [
                    'type' => 'bigint',
                    'name' => 'bigint',
                    'default' => '0',
                    'padding' => '13',
                    'nullable' => 'true',
                    'unsigned' => 'false',
                ],
                'float' => [
                    'type' => 'float',
                    'name' => 'float',
                    'default' => '0',
                    'scale' => '4',
                    'precision' => '12',
                ],
                'double' => [
                    'type' => 'decimal',
                    'name' => 'double',
                    'default' => '11111111.111111',
                    'precision' => '14',
                    'scale' => '6',
                ],
                'decimal' => [
                    'type' => 'decimal',
                    'name' => 'decimal',
                    'default' => '0',
                    'scale' => '4',
                    'precision' => '15',
                ],
                'date' => [
                    'type' => 'date',
                    'name' => 'date',
                ],
                'timestamp' => [
                    'type' => 'timestamp',
                    'name' => 'timestamp',
                    'default' => 'CURRENT_TIMESTAMP',
                    'on_update' => 'true',
                ],
                'datetime' => [
                    'type' => 'datetime',
                    'name' => 'datetime',
                    'default' => '0',
                ],
                'longtext' => [
                    'type' => 'longtext',
                    'name' => 'longtext',
                ],
                'mediumtext' => [
                    'type' => 'mediumtext',
                    'name' => 'mediumtext',
                ],
                'char' => [
                    'type' => 'char',
                    'name' => 'char',
                    'length' => '255',
                    'nullable' => 'true',
                ],
                'varchar' => [
                    'type' => 'varchar',
                    'name' => 'varchar',
                    'length' => '254',
                    'nullable' => 'true',
                ],
                'mediumblob' => [
                    'type' => 'mediumblob',
                    'name' => 'mediumblob',
                ],
                'blob' => [
                    'type' => 'blob',
                    'name' => 'blob',
                ],
                'boolean' => [
                    'type' => 'boolean',
                    'name' => 'boolean',
                ],
                'varbinary_rename' => [
                    'type' => 'varbinary',
                    'name' => 'varbinary_rename',
                    'default' => '10101',
                    'disabled' => 'true',
                ],
            ],
            'constraint' => [
                'TEST_TABLE_UNIQUE' => [
                    'column' => [
                        'smallint' => 'smallint',
                        'bigint' => 'bigint',
                    ],
                    'type' => 'unique',
                    'referenceId' => 'TEST_TABLE_UNIQUE',
                ],
                'TEST_TABLE_TINYINT_REFERENCE' => [
                    'type' => 'foreign',
                    'referenceId' => 'TEST_TABLE_TINYINT_REFERENCE',
                    'column' => 'tinyint',
                    'table' => 'test_table',
                    'referenceTable' => 'reference_table',
                    'referenceColumn' => 'tinyint_ref',
                    'onDelete' => 'NO ACTION',
                ],
            ],
            'index' => [
                'TEST_TABLE_INDEX' => [
                    'column' => [
                        'tinyint' => 'tinyint',
                        'bigint' => 'bigint',
                    ],
                    'referenceId' => 'TEST_TABLE_INDEX',
                    'indexType' => 'btree',
                ],
            ],
            'name' => 'test_table',
            'resource' => 'default',
        ],
    ],
];
