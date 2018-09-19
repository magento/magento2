<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Config\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Config $config */
$config = $objectManager->get(Config::class);
$config->setDataByPath('payment/' . ConfigProvider::PAYPAL_CODE . '/active', 1);
$config->save();
$config->setDataByPath('payment/' . ConfigProvider::PAYPAL_VAULT_CODE . '/active', 1);
$config->save();
