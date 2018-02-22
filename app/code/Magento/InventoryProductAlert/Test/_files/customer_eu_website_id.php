<?php
/**
 * Create customer and attach it to custom website with code newwebsite
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Store\Model\Website $website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->load('eu_website');
$websiteId = $website->getId();

$customer = $objectManager->create(\Magento\Customer\Model\Customer::class);
/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(
    $websiteId
)->setId(
    2
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
$addressOne = $objectManager->create(\Magento\Customer\Model\Address::class);
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
$addressTwo = $objectManager->create(\Magento\Customer\Model\Address::class);
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

$customer->save();
