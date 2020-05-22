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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Vault\Model\PaymentToken;

Resolver::getInstance()->requireDataFixture('Magento/Vault/_files/token.php');
/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var PaymentToken $token */
$token = $objectManager->create(PaymentToken::class);
$token->load('vault_payment', 'payment_method_code');
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
