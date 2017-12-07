<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'setup_tests_entity_table' => [
        ['entity_id' => '1',
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
            'dob' => '1973-12-15'
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
        ]
    ]
];
