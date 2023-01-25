<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Vault\Model\PaymentToken;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Vault/_files/customer.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$paymentTokens = [
    [
        'customer_id' => 1,
        'public_hash' => '1234',
        'payment_method_code' => 'first',
        'type' => 'simple',
        'expires_at' => '2016-09-04 10:18:15',
        'is_active' => 1
    ],
    [
        'customer_id' => 1,
        'public_hash' => '12345',
        'payment_method_code' => 'second',
        'type' => 'simple',
        'expires_at' => '2016-10-04 10:18:15',
        'is_active' => 1
    ],
    [
        'customer_id' => 1,
        'public_hash' => '23456',
        'payment_method_code' => 'third',
        'type' => 'notsimple',
        'expires_at' => '2016-11-04 10:18:15',
        'is_active' => 1
    ],
    [
        'customer_id' => 1,
        'public_hash' => '234567',
        'payment_method_code' => 'fourth',
        'type' => 'simple',
        'expires_at' => '2016-12-04 10:18:15',
        'is_active' => 0
    ],
    [
        'customer_id' => 1,
        'public_hash' => '34567',
        'payment_method_code' => 'fifth',
        'type' => 'card',
        'expires_at' => date('Y-m-d h:i:s', strtotime('+1 month')),
        'is_active' => 1
    ],
    [
        'customer_id' => 1,
        'public_hash' => '345678',
        'payment_method_code' => 'sixth',
        'type' => 'account',
        'expires_at' => date('Y-m-d h:i:s', strtotime('+1 month')),
        'is_active' => 1
    ],
];
/** @var array $tokenData */
foreach ($paymentTokens as $tokenData) {
    /** @var PaymentToken $bookmark */
    $paymentToken = $objectManager->create(PaymentToken::class);
    $paymentToken
        ->setData($tokenData)
        ->save();
}
