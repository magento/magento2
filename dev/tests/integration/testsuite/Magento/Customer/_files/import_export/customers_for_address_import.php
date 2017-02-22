<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//Create customer
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
$customer->setWebsiteId(
    0
)->setEntityId(
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'BetsyParker@example.com'
)->setPassword(
    'password'
)->setGroupId(
    0
)->setStoreId(
    0
)->setIsActive(
    1
)->setFirstname(
    'Betsy'
)->setLastname(
    'Parker'
)->setGender(
    2
);
$customer->isObjectNew(true);
$customer->save();

// Create and set addresses
$addressFirst = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressFirst->addData(
    [
        'entity_id' => 1,
        'firstname' => 'Betsy',
        'lastname' => 'Parker',
        'street' => '1079 Rocky Road',
        'city' => 'Philadelphia',
        'country_id' => 'US',
        'region_id' => '51',
        'postcode' => '19107',
        'telephone' => '215-629-9720',
    ]
);
$addressFirst->isObjectNew(true);
$customer->addAddress($addressFirst);
$customer->setDefaultBilling($addressFirst->getId());

$addressSecond = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressSecond->addData(
    [
        'entity_id' => 2,
        'firstname' => 'Anthony',
        'lastname' => 'Nealy',
        'street' => '3176 Cambridge Court',
        'city' => 'Fayetteville',
        'country_id' => 'US',
        'region_id' => '5',
        'postcode' => '72701',
        'telephone' => '479-899-9849',
    ]
);
$addressSecond->isObjectNew(true);
$customer->addAddress($addressSecond);
$customer->setDefaultShipping($addressSecond->getId());
$customer->isObjectNew(true);
$customer->save();
