<?php

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer  $configWriter */
$configWriter = $objectManager->create(WriterInterface::class);

$configWriter->delete('carriers/flatrate/active');
$configWriter->delete('carriers/tablerate/active');
$configWriter->delete('carriers/freeshipping/active');
$configWriter->delete('carriers/ups/active');
$configWriter->delete('carriers/usps/active');
$configWriter->delete('carriers/fedex/active');
$configWriter->delete('carriers/dhl/active');
