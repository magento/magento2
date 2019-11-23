<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Quote\Model\Quote\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Braintree\Model\Ui\ConfigProvider;

/**
 * @var Magento\Quote\Model\Quote $quote
 */

if (empty($quote)) {
    throw new \Exception('$quote should be defined in the parent fixture');
}

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var PaymentInterface $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(ConfigProvider::CODE);
$quote->setPayment($payment);
