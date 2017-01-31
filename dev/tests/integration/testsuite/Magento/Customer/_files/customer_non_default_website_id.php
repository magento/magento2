<?php
/**
 * Create customer and attach it to custom website with code newwebsite
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * @var \Magento\Store\Model\Website $website
 */
$website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Website');
$website->setName('new Website')->setCode('newwebsite')->save();

$websiteId = $website->getId();
$storeManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Store\Model\StoreManager::class);
$storeManager->reinitStores();
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(
    $websiteId
)->setId(
        1
    )->setEntityTypeId(
        1
    )->setAttributeSetId(
        1
    )->setEmail(
        'customer2@example.com'
    )->setPassword(
        'password'
    )->setGroupId(
        1
    )->setStoreId(
        $website->getStoreId()
    )->setIsActive(
        1
    )->setFirstname(
        'Firstname'
    )->setLastname(
        'Lastname'
    )->setDefaultBilling(
        1
    )->setDefaultShipping(
        1
    );
$customer->isObjectNew(true);

/** @var \Magento\Customer\Model\Address $addressOne  */
$addressOne = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressOneData = [
    'firstname' => 'Firstname',
    'lastname' => 'LastName',
    'street' => ['test street'],
    'city' => 'test city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 1,
];
$addressOne->setData($addressOneData);
$customer->addAddress($addressOne);

/** @var \Magento\Customer\Model\Address $addressTwo  */
$addressTwo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressTwoData = [
    'firstname' => 'test firstname',
    'lastname' => 'test lastname',
    'street' => ['test street'],
    'city' => 'test city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 2,
];
$addressTwo->setData($addressTwoData);
$customer->addAddress($addressTwo);

/** @var \Magento\Customer\Model\Address $addressThree  */
$addressThree = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Address');
$addressThreeData = [
    'firstname' => 'removed firstname',
    'lastname' => 'removed lastname',
    'street' => ['removed street'],
    'city' => 'removed city',
    'country_id' => 'US',
    'postcode' => '01001',
    'telephone' => '+7000000001',
    'entity_id' => 3,
];
$addressThree->setData($addressThreeData);
$customer->addAddress($addressThree);
$customer->save();
