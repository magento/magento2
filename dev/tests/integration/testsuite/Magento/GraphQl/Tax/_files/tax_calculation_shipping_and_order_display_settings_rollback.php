<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);

//Apply discount on prices to include tax
$configWriter->save('tax/classes/shipping_tax_class', '0');
$configWriter->save('tax/calculation/shipping_includes_tax', '0');
$configWriter->save('tax/sales_display/shipping', '1');
$configWriter->save('tax/display/shipping', '1');
$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
