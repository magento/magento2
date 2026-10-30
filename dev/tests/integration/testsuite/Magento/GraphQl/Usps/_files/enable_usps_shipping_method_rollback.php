<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer  $configWriter */
$configWriter = $objectManager->create(WriterInterface::class);

$configWriter->delete('carriers/usps/active');
$configWriter->delete(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP);
$configWriter->delete(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP);
$configWriter->delete('carriers/usps/usps_type');
$configWriter->delete('carriers/usps/allowed_methods');
