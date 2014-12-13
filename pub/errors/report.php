<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require_once 'processorFactory.php';

$processorFactory = new \Magento\Framework\Error\ProcessorFactory();
$processor = $processorFactory->createProcessor();
if (isset($reportData) && is_array($reportData)) {
    $processor->saveReport($reportData);
}
$response = $processor->processReport();
$response->sendResponse();
