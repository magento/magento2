<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote_with_customer.php');

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = $objectManager->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test01', 'reserved_order_id');
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
