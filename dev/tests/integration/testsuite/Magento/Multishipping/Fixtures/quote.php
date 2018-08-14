<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);

require __DIR__ . '/shipping_address_list.php';
require __DIR__ . '/billing_address.php';
require __DIR__ . '/payment_method.php';
require __DIR__ . '/items.php';

$quote->setReservedOrderId('multishipping_quote_id')
    ->setCustomerEmail('customer001@test.com');

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quote->collectTotals();
$quoteRepository->save($quote);
