<?php
/**
 * Create customer and attach it to custom website with code eu_website
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

$customer->save();
