<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Directory\Model\Currency;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$rates = ['USD' => ['EUR' => '0.7000']];
/** @var Currency $currencyModel */
$currencyModel = $objectManager->create(Currency::class);
$currencyModel->saveRates($rates);
