<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require __DIR__ . '/../../Sales/_files/quote_with_customer.php';

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

$quote->getPayment()
    ->setMethod('checkmo');
$quote->getShippingAddress()
    ->setShippingMethod('flatrate_flatrate')
    ->setCollectShippingRates(true);
$quote->collectTotals();

$quote->setCustomerEmail('');

/** @var CartRepositoryInterface $repository */
$repository = $objectManager->get(CartRepositoryInterface::class);
$repository->save($quote);
