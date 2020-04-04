<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;

$order = include __DIR__ . '/../../AuthorizenetAcceptjs/_files/full_order.php';

$objectManager = Bootstrap::getObjectManager();
$cardinalJWT = include __DIR__ . '/response/cardinal_jwt.php';

/** @var Payment $payment */
$payment = $order->getPayment();
$payment->setAdditionalInformation('cardinalJWT', $cardinalJWT);

return $order;
