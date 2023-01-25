<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Config\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Model\AccountPaymentTokenFactory;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Customer\Model\CustomerRegistry;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = Bootstrap::getObjectManager()->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
/** @var Config $config */
$config = $objectManager->get(Config::class);
$config->setDataByPath('payment/payflowpro/active', 1);
$config->save();
$config->setDataByPath('payment/payflowpro_cc_vault/active', 1);
$config->save();

/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

/** @var PaymentToken $paymentToken */
$paymentToken = $objectManager->create(PaymentToken::class);
$paymentToken
    ->setCustomerId($customer->getId())
    ->setPaymentMethodCode('payflowpro')
    ->setType(AccountPaymentTokenFactory::TOKEN_TYPE_ACCOUNT)
    ->setGatewayToken('mx29vk')
    ->setPublicHash($encryptor->hash($customer->getId()))
    ->setTokenDetails(json_encode(['payerEmail' => 'john.doe@example.com']))
    ->setIsActive(true)
    ->setIsVisible(true)
    ->setExpiresAt(date('Y-m-d H:i:s', strtotime('+1 year')));

/** @var PaymentTokenRepository $tokenRepository */
$tokenRepository = $objectManager->create(PaymentTokenRepository::class);
$tokenRepository->save($paymentToken);
