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
/** @var Writer  $configWriter */
$configWriter = $objectManager->create(WriterInterface::class);

$configWriter->save('carriers/flatrate/active', 1);
$configWriter->save('carriers/tablerate/active', 1);
$configWriter->save('carriers/freeshipping/active', 1);
$configWriter->save('carriers/ups/active', 1);
$configWriter->save('carriers/usps/active', 1);
$configWriter->save('carriers/fedex/active', 1);
$configWriter->save('carriers/dhl/active', 1);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
