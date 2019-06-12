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
use Magento\TestFramework\ObjectManager;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * @var Magento\Quote\Model\Quote $quote
 */

if (empty($quote)) {
    throw new \Exception('$quote should be defined in the parent fixture');
}

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var PaymentInterface $payment */
$payment = $objectManager->create(PaymentInterface::class);
$payment->setMethod('checkmo');

$quote->setPayment($payment);
