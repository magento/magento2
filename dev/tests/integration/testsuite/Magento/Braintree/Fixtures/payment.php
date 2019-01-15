<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

require __DIR__ . '/../../Vault/_files/token.php';

$token->setPaymentMethodCode(ConfigProvider::CODE);
/** @var OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory */
$paymentExtensionFactory = $objectManager->get(OrderPaymentExtensionInterfaceFactory::class);
$extensionAttributes = $paymentExtensionFactory->create();
$extensionAttributes->setVaultPaymentToken($token);

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod(ConfigProvider::CODE);
$payment->setExtensionAttributes($extensionAttributes);
$payment->setAuthorizationTransaction(true);
