<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'setup_tests_entity_table' => [
        [
            'entity_id' => '1',
            'website_id' => '1',
            'email_field' => 'entity@example.com',
            'increment_id' => null,
            'created_at' => '2017-10-30 09:41:25',
            'updated_at' => '2017-10-30 09:45:05',
            'created_in' => 'Default Store View',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'default_billing_address_id' => '1',
            'default_shipping_address_id' => '1',
            'group_id' => '0',
            'store_id' => '0',
        ],
    ],
    'setup_tests_address_entity' => [
        [
            'entity_id' => '1',
            'increment_id' => null,
            'parent_id' => '1',
            'created_at' => '2017-10-30 09:45:05',
            'updated_at' => '2017-10-30 09:45:05',
            'is_active' => '1',
            'city' => 'city',
            'company' => 'Magento',
            'country_id' => 'US',
            'fax' => null,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'middlename' => null,
            'postcode' => '90210',
            'prefix' => null,
            'region' => 'Alabama',
            'region_id' => '1',
            'street' => 'street1',
            'suffix' => null,
            'telephone' => '12345678',
        ],
        [
            'entity_id' => '2',
            'increment_id' => null,
            'parent_id' => '1',
            'created_at' => '2017-10-30 13:34:19',
            'updated_at' => '2017-10-30 13:34:19',
            'is_active' => '1',
            'city' => 'Austin',
            'company' => 'X.Commerce',
            'country_id' => 'US',
            'fax' => null,
            'firstname' => 'Joan',
            'lastname' => 'Doe',
            'middlename' => null,
            'postcode' => '36351',
            'prefix' => null,
            'region' => 'Alabama',
            'region_id' => '1',
            'street' => 'New Brockton',
            'suffix' => null,
            'telephone' => '12345678',

        ],
    ],
    'setup_tests_entity_passwords' => [
        [
            'password_id' => '1',
            'entity_id' => '1',
            'password_hash' => '139e2ee2785cd9d9eb5714a02aca579bbcc05f9062996389d6e0e329bab9841b',
        ]
    ]
];
