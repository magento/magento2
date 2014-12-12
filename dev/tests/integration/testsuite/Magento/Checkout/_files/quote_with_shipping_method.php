<?php
/**
 * Quote with simple product, shipping, billing addresses and shipping method fixture
 *
 * The quote is not saved inside the original fixture. It is later saved inside child fixtures, but along with some
 * additional data which may break some tests.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require 'quote_with_address_saved.php';

$quote->load('test_order_1', 'reserved_order_id');
$shippingAddress = $quote->getShippingAddress();
$shippingAddress->setShippingMethod('flatrate_flatrate')
    ->setShippingDescription('Flat Rate - Fixed')
    ->setShippingAmount(10.0)
    ->setBaseShippingAmount(12.0)
    ->save();
