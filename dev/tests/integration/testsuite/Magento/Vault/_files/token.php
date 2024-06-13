<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenRepository;

$objectManager = Bootstrap::getObjectManager();

/** @var PaymentToken $token */
$token = $objectManager->create(PaymentToken::class);

$token->setGatewayToken('gateway_token')
    ->setPublicHash('public_hash')
    ->setPaymentMethodCode('vault_payment')
    ->setType('card')
    ->setExpiresAt(strtotime('+1 year'))
    ->setIsVisible(true)
    ->setIsActive(true);

/** @var PaymentTokenRepository $tokenRepository */
$tokenRepository = $objectManager->create(PaymentTokenRepository::class);
$token = $tokenRepository->save($token);
