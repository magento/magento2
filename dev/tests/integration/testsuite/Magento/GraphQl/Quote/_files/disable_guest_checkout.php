<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
/**
 * @deprecated use next @magentoConfigFixture instead:
 * @magentoConfigFixture default_store checkout/options/guest_checkout 0
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);

$configWriter->save('checkout/options/guest_checkout', 0);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
