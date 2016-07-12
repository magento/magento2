<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Config\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Vault\Model\AccountPaymentTokenFactory;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

/** @var Config $config */
$config = $objectManager->get(Config::class);
$config->setDataByPath('payment/' . ConfigProvider::PAYPAL_CODE . '/active', 1);
$config->save();
$config->setDataByPath('payment/' . ConfigProvider::PAYPAL_VAULT_CODE . '/active', 1);
$config->save();

/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

/** @var PaymentToken $paymentToken */
$paymentToken = $objectManager->create(PaymentToken::class);
$paymentToken
    ->setCustomerId($customer->getId())
    ->setPaymentMethodCode(ConfigProvider::PAYPAL_CODE)
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