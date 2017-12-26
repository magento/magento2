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
                    'type' => 'tinyinteger',
                    'name' => 'tinyint_ref',
                    'renameTo' => 'tinyintref_2',
                    'default' => '0',
                    'padding' => '7',
                    'nullable' => 'true',
                    'unsigned' => 'false',
                ],
            ],
            'constraint' => [
                'tinyint_primary' => [
                    'column' => [
                        'tinyint_ref' => 'tinyint_ref',
                    ],
                    'type' => 'primary',
                    'name' => 'tinyint_primary',
                ],
            ],
            'name' => 'reference_table',
            'resource' => 'sales',
        ],
        'test_table' => [
            'column' => [
                'smallint' => [
                    'type' => 'smallinteger',
                    'name' => 'smallint',
                    'default' => '0',
                    'padding' => '3',
                ],
                'tinyint' => [
                    'type' => 'tinyinteger',
                    'name' => 'tinyint',
                    'default' => '0',
                    'padding' => '7',
                    'nullable' => 'true',
                    'unsigned' => 'false',
                ],
                'bigint' => [
                    'type' => 'biginteger',
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
                    'scale' => '12',
                ],
                'double' => [
                    'type' => 'double',
                    'name' => 'double',
                    'default' => '11111111.111111',
                    'scale' => '245',
                ],
                'decimal' => [
                    'type' => 'decimal',
                    'name' => 'decimal',
                    'default' => '0',
                    'scale' => '15',
                    'precission' => '4',
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
                    'length' => '111111111111',
                ],
                'mediumtext' => [
                    'type' => 'mediumtext',
                    'name' => 'mediumtext',
                    'length' => '11222222',
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
                    'length' => '1122222',
                ],
                'blob' => [
                    'type' => 'blob',
                    'name' => 'blob',
                    'length' => '1122',
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
                    'renameTo' => 'varbinary_renamed',
                ],
            ],
            'constraint' => [
                'some_unique_key' => [
                    'column' =>
                        [
                            'smallint' => 'smallint',
                            'bigint' => 'bigint',
                        ],
                        'type' => 'unique',
                        'name' => 'some_unique_key',
                ],
                'some_foreign_key' => [
                    'type' => 'foreign',
                    'name' => 'some_foreign_key',
                    'column' => 'tinyint',
                    'table' => 'test_table',
                    'referenceTable' => 'reference_table',
                    'referenceColumn' => 'tinyint_ref',
                    'onDelete' => 'NO ACTION',
                ],
            ],
            'index' => [
                'speedup_index' => [
                    'column' => [
                        'tinyint' => 'tinyint',
                        'bigint' => 'bigint',
                    ],
                    'name' => 'speedup_index',
                    'type' => 'btree',
                ],
            ],
            'name' => 'test_table',
            'resource' => 'sales',
        ],
    ],
];