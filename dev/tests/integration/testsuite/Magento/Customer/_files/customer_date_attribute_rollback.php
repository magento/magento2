<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $repository \Magento\Customer\Api\CustomerRepositoryInterface */
$repository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');

$customer = $objectManager->create('Magento\Customer\Model\Customer');
$customer1 = $repository->get('john.doe1@ex.com', 1);
$repository->delete($customer1);

$customer2 = $repository->get('john.doe2@ex.com', 1);
$repository->delete($customer2);

$customer3 = $repository->get('john.doe3@ex.com', 1);
$repository->delete($customer3);
