<?php
/**
 * Quote with simple product, shipping, billing addresses and shipping method fixture
 *
 * The quote is not saved inside the original fixture. It is later saved inside child fixtures, but along with some
 * additional data which may break some tests.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address_saved.php');

$objectManager = Bootstrap::getObjectManager();
/** @var QuoteFactory $quoteFactory */
$quoteFactory = $objectManager->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = $objectManager->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
$rate = $objectManager->get(\Magento\Quote\Model\Quote\Address\Rate::class);

$shippingAddress = $quote->getShippingAddress();
$shippingAddress->setShippingMethod('flatrate_flatrate')
    ->setShippingDescription('Flat Rate - Fixed')
    ->save();

$rate->setPrice(0)
    ->setAddressId($shippingAddress->getId())
    ->save();
$shippingAddress->setBaseShippingAmount($rate->getPrice());
$shippingAddress->setShippingAmount($rate->getPrice());
$rate->delete();
