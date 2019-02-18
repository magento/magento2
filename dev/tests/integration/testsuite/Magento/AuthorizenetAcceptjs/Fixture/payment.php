<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(Config::METHOD);
$payment->setAuthorizationTransaction(true);
