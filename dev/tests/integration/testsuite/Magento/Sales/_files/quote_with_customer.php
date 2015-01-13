<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

include __DIR__ . '/quote.php';
include __DIR__ . '/../../../Magento/Customer/_files/customer.php';

/** @var $quote \Magento\Sales\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Quote');
$quote->load('test01', 'reserved_order_id');
/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customer */
$customerRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Customer\Api\CustomerRepositoryInterface');
$customer = $customerRepository->getById(1);
$quote->setCustomer($customer)->setCustomerIsGuest(false)->save();
