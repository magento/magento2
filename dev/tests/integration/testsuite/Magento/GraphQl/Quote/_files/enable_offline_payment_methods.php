<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @deprecated use next @magentoConfigFixture instead:
 * @magentoConfigFixture default_store payment/banktransfer/active 1
 * @magentoConfigFixture default_store payment/cashondelivery/active 1
 * @magentoConfigFixture default_store payment/checkmo/active 1
 * @magentoConfigFixture default_store payment/purchaseorder/active 1
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);

$configWriter->save('payment/banktransfer/active', 1);
$configWriter->save('payment/cashondelivery/active', 1);
$configWriter->save('payment/checkmo/active', 1);
$configWriter->save('payment/purchaseorder/active', 1);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
