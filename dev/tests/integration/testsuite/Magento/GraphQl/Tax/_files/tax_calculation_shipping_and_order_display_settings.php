<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);

//configuration setting for shipping tax class and shipping tax calculation and display
$configWriter->save('tax/classes/shipping_tax_class', '2');
$configWriter->save('tax/calculation/shipping_includes_tax', '1');
$configWriter->save('tax/sales_display/shipping', '3');
$configWriter->save('tax/display/shipping', '3');

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
