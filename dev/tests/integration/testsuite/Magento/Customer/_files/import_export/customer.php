<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//Create customer
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Customer::class
);
$customer->setWebsiteId(
    1
)->setEntityId(
    1
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'CharlesTAlston@teleworm.us'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setIsActive(
    1
)->setFirstname(
    'Charles'
)->setLastname(
    'Alston'
)->setGender(
    2
);
$customer->isObjectNew(true);

// Create address
$address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Address::class);
//  default_billing and default_shipping information would not be saved, it is needed only for simple check
$address->addData(
    [
        'firstname' => 'Charles',
        'lastname' => 'Alston',
        'street' => '3781 Neuport Lane',
        'city' => 'Panola',
        'country_id' => 'US',
        'region_id' => '51',
        'postcode' => '30058',
        'telephone' => '770-322-3514',
        'default_billing' => 1,
        'default_shipping' => 1,
    ]
);

// Assign customer and address
$customer->addAddress($address);
$customer->save();

// Mark last address as default billing and default shipping for current customer
$customer->setDefaultBilling($address->getId());
$customer->setDefaultShipping($address->getId());
$customer->save();

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$objectManager->get(\Magento\Framework\Registry::class)->unregister('_fixture/Magento_ImportExport_Customer');
$objectManager->get(\Magento\Framework\Registry::class)->register('_fixture/Magento_ImportExport_Customer', $customer);
