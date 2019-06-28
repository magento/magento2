<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;

$objectManager = Bootstrap::getObjectManager();

$adapterFactory = $objectManager->get(\Magento\Braintree\Model\Adapter\BraintreeAdapterFactory::class);
$adapter = $adapterFactory->create();

$result = $adapter->sale(
    [
        'amount' => '0.01',
        'customer' => [
            'email' => 'customer@example.com',
            'firstName' => 'John',
            'lastName' => 'Smith'
        ],
        'options' => ['storeInVaultOnSuccess' => true],
        'paymentMethodNonce' => 'fake-valid-nonce',
    ]
);

$braintreeToken = $result->transaction->creditCardDetails->token;

/** @var PaymentToken $token */
$token = $objectManager->create(PaymentToken::class);

$token->setGatewayToken($braintreeToken)
    ->setPublicHash('braintree_public_hash')
    ->setPaymentMethodCode('braintree_vault')
    ->setType('card')
    ->setExpiresAt(strtotime('+1 year'))
    ->setIsVisible(true)
    ->setIsActive(true)
    ->setCustomerId(1);

/** @var PaymentTokenRepository $tokenRepository */
$tokenRepository = $objectManager->create(PaymentTokenRepository::class);
$token = $tokenRepository->save($token);
