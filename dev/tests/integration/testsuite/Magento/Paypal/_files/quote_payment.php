<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
$quote->load('test01', 'reserved_order_id');

$payment = $quote->getPayment();
$payment->setMethod(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS)
    ->setAdditionalInformation(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID, 123);
$quote->collectTotals()->save();
