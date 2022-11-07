<?php
/**
 * Customer address fixture with postcode 36104
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Customer\Model\Address $customerAddress */
$customerAddress = $objectManager->create(\Magento\Customer\Model\Address::class);
/** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'entity_id' => 1,
        'attribute_set_id' => 2,
        'telephone' => 3342423935,
        'postcode' => 36104,
        'country_id' => 'US',
        'city' => 'Montgomery',
        'company' => 'Govt',
        'street' => 'Alabama State Capitol',
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => 1,
        'region_id' => 1,
    ]
);
$customerAddress->save();

/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
$customerAddress = $addressRepository->getById(1);
$customerAddress->setCustomerId(1);
$customerAddress = $addressRepository->save($customerAddress);


/** @var \Magento\Customer\Model\Customer $customer */
$customer = $objectManager->create(
    \Magento\Customer\Model\Customer::class
)->load($customerAddress->getCustomerId());
$customer->setDefaultShipping(1);
$customer->save();

$customerRegistry->remove($customerAddress->getCustomerId());
/** @var \Magento\Customer\Model\AddressRegistry $addressRegistry */
$addressRegistry = $objectManager->get(\Magento\Customer\Model\AddressRegistry::class);
$addressRegistry->remove($customerAddress->getId());
