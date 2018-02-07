<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once 'processorFactory.php';

$processorFactory = new \Magento\Framework\Error\ProcessorFactory();
$processor = $processorFactory->createProcessor();
if (isset($reportData) && is_array($reportData)) {
    $processor->saveReport($reportData);
}
$response = $processor->processReport();
$response->sendResponse();
