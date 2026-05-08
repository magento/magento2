<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Directory\Model\RemoveCurrencyRateByCode;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var RemoveCurrencyRateByCode $deleteRateByCode */
$deleteRateByCode = $objectManager->get(RemoveCurrencyRateByCode::class);
$deleteRateByCode->execute('CNY');
