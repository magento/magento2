<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
$repository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
$customer = $objectManager->create('Magento\Customer\Model\Customer');

/** @var Magento\Customer\Model\Customer $customer */
$customer->setWebsiteId(1)
    ->setId(1)
    ->setEntityTypeId(1)
    ->setAttributeSetId(1)
    ->setEmail('customer@example.com')
    ->setPassword('password')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setFirstname('John')
    ->setLastname('Smith')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1);
$customer->isObjectNew(true);
$customer->save();
