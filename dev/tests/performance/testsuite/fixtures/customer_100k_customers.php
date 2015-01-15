<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\TestFramework\Application $this */

if (!isset($customersNumber)) {
    $customersNumber = 100000;
}

$pattern = [
    'email' => 'user%s@example.com',
    '_website' => 'base',
    '_store' => '',
    'confirmation' => null,
    'created_at' => '30-08-2012 17:43',
    'created_in' => 'Default',
    'default_billing' => '1',
    'default_shipping' => '1',
    'disable_auto_group_change' => '0',
    'dob' => '12-10-1991',
    'firstname' => 'Firstname %s',
    'gender' => 'Male',
    'group_id' => '1',
    'lastname' => 'Lastname %s',
    'middlename' => '',
    'password_hash' => '',
    'prefix' => null,
    'rp_token' => null,
    'rp_token_created_at' => null,
    'store_id' => '0',
    'suffix' => null,
    'taxvat' => null,
    'website_id' => '1',
    'password' => '123123q%s',
    '_address_city' => 'Fayetteville',
    '_address_company' => '',
    '_address_country_id' => 'US',
    '_address_fax' => '',
    '_address_firstname' => 'Anthony',
    '_address_lastname' => 'Nealy',
    '_address_middlename' => '',
    '_address_postcode' => '%s',
    '_address_prefix' => '',
    '_address_region' => 'Arkansas',
    '_address_street' => '%s Freedom Blvd. #%s',
    '_address_suffix' => '',
    '_address_telephone' => '%s-%s-%s',
    '_address_vat_id' => '',
    '_address_default_billing_' => '1',
    '_address_default_shipping_' => '1',
];
$generator = new \Magento\TestFramework\ImportExport\Fixture\Generator($pattern, $customersNumber);
/** @var \Magento\ImportExport\Model\Import $import */
$import = $this->getObjectManager()->create(
    'Magento\ImportExport\Model\Import',
    ['data' => ['entity' => 'customer_composite', 'behavior' => 'append']]
);
// it is not obvious, but the validateSource() will actually save import queue data to DB
$import->validateSource($generator);
// this converts import queue into actual entities
$import->importSource();
