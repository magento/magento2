<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
$tokenRepository->save($token);
