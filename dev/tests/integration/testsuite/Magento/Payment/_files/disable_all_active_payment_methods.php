<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$processConfigData = function (Config $config, array $data) {
    foreach ($data as $key => $value) {
        $config->setDataByPath($key, $value);
        $config->save();
    }
};

$objectManager          = Bootstrap::getObjectManager();
$paymentMethodList      = $objectManager->get(\Magento\Payment\Api\PaymentMethodListInterface::class);
$rollbackConfigKey      = 'test/payment/disabled_payment_methods';
$configData             = [];
$disabledPaymentMethods = [];

// Get all active Payment Methods
foreach ($paymentMethodList->getActiveList(\Magento\Store\Model\Store::DEFAULT_STORE_ID) as $paymentMethod) {
    $configData['payment/' . $paymentMethod->getCode() . '/active'] = 0;
    $disabledPaymentMethods[] = $paymentMethod->getCode();
}
// Remember all manually disabled Payment Methods for rollback
$configData[$rollbackConfigKey] = implode(',', $disabledPaymentMethods);

/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

$processConfigData($defConfig, $configData);
