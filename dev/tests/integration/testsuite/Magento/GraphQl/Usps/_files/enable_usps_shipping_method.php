<?php
/**
 * Copyright 2025 Adobe
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

$configWriter->save('carriers/usps/active', 1);
$configWriter->save('carriers/usps/usps_type', 'USPS_XML');
$allowedMethods = '0_FCLE,0_FCL,0_FCP,1,2,3,4,6,7,13,16,17,22,23,25,27,28,33,34,35,36,37,42,43,53,57,61,';
$allowedMethods .= 'INT_1,INT_2,INT_4,INT_6,INT_7,INT_8,INT_9,INT_10,INT_11,INT_12,INT_13,INT_14,INT_15,INT_16,INT_20,';
$allowedMethods .= '1058,4058,6058,2058,4096,1096,2096,6096';
$configWriter->save('carriers/usps/allowed_methods', $allowedMethods);
$configWriter->save(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP, '90210');
$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
