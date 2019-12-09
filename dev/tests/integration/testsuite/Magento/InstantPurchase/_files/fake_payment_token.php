<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/** @var PaymentTokenRepositoryInterface $repository */
$repository = Bootstrap::getObjectManager()->get(PaymentTokenRepositoryInterface::class);
/** @var PaymentTokenInterface $token */
$token = Bootstrap::getObjectManager()->create(PaymentTokenInterface::class);
$token->setCustomerId(1);
$token->setPaymentMethodCode('fake');
$token->setPublicHash('fakePublicHash');
$token->setIsActive(true);
$token->setIsVisible(true);
$token->setCreatedAt(strtotime('-1 day'));
$token->setExpiresAt(strtotime('+1 day'));
$tokenDetails = ['cc_last4' => '1111', 'cc_exp_year' => '2020', 'cc_exp_month' => '01', 'cc_type' => 'VI'];
$token->setTokenDetails(json_encode($tokenDetails));
$repository->save($token);
