<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');

/** @var $quote \Magento\Quote\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->load('test01', 'reserved_order_id');
/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customer */
$customerRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customerId = 1;
$customer = $customerRepository->getById($customerId);
$quote->setCustomer($customer)->setCustomerIsGuest(false)->save();
foreach ($quote->getAllAddresses() as $address) {
    $address->setCustomerId($customerId)->save();
}

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
