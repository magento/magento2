<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once 'processorFactory.php';

$processorFactory = new \Magento\Framework\Error\ProcessorFactory();
$processor = $processorFactory->createProcessor();
$reportId = (isset($_GET['id'])) ? (int)$_GET['id'] : null;

if ($reportId) {
    try {
        $processor->loadReport($reportId);
    } catch (\Exception $e) {
        header('Location: '. $processor->getBaseUrl());
        exit;
    }
}

if (isset($reportData) && is_array($reportData)) {
    $reportUrl = $processor->saveReport($reportData);
    if (headers_sent()) {
        echo '<script type="text/javascript">';
        echo "window.location.href = '{$reportUrl}';";
        echo '</script>';
        exit;
    }
}

$response = $processor->processReport();
$response->sendResponse();
