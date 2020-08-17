<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @deprecated use next @magentoConfigFixture instead:
 * @magentoConfigFixture default_store payment/fake_vault/active 0
 * @magentoConfigFixture default_store payment/paypal_billing_agreement/active 0
 * @magentoConfigFixture default_store payment/fake/active 0
 * @magentoConfigFixture default_store payment/checkmo/active 0
 * @magentoConfigFixture default_store payment/free/active 0
 *
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$paymentMethodList = $objectManager->get(\Magento\Payment\Api\PaymentMethodListInterface::class);
$rollbackConfigKey = 'test/payment/disabled_payment_methods';
$configData = [];
$disabledPaymentMethods = [];

// Get all active Payment Methods
foreach ($paymentMethodList->getActiveList(Store::DEFAULT_STORE_ID) as $paymentMethod) {
    $configData['payment/' . $paymentMethod->getCode() . '/active'] = 0;
    $disabledPaymentMethods[] = $paymentMethod->getCode();
}
// Remember all manually disabled Payment Methods for rollback
$configData[$rollbackConfigKey] = implode(',', $disabledPaymentMethods);

/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

foreach ($configData as $key => $value) {
    $defConfig->setDataByPath($key, $value);
    $defConfig->save();
}
