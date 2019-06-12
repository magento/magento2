<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Checkout/_files/quote_with_address.php';

/** @var \Magento\Quote\Model\Quote $quote */
$quote->addProduct(
    $customDesignProduct->load($customDesignProduct->getId()),
    1
);

$quote->getPayment()->setMethod('payflowpro');
$quote->setIsMultiShipping('0');
$quote->getShippingAddress()->setShippingMethod('freeshipping_freeshipping');
$quote->setReservedOrderId('test01');
$quote->collectTotals()
    ->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
