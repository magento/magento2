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

require 'quote_with_address_saved.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$rate = $objectManager->get(\Magento\Quote\Model\Quote\Address\Rate::class);

$quote->load('test_order_1', 'reserved_order_id');
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
