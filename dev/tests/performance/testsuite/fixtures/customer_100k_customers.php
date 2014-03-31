<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var \Magento\TestFramework\Application $this */

if (!isset($customersNumber)) {
    $customersNumber = 100000;
}

$pattern = array(
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
    '_address_default_shipping_' => '1'
);
$generator = new \Magento\TestFramework\ImportExport\Fixture\Generator($pattern, $customersNumber);
/** @var \Magento\ImportExport\Model\Import $import */
$import = $this->getObjectManager()->create(
    'Magento\ImportExport\Model\Import',
    array('data' => array('entity' => 'customer_composite', 'behavior' => 'append'))
);
// it is not obvious, but the validateSource() will actually save import queue data to DB
$import->validateSource($generator);
// this converts import queue into actual entities
$import->importSource();
