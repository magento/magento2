<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once('b2c_mappings.php');
require_once('common.php');

generateReport(
    $mapping,
    function ($fp, $aggregatedResult, $headersArray, $executionTime) {
        $pageViews = 0;
        $checkoutCount = 0;
        foreach ($aggregatedResult as $row) {
            if ($row['is_storefront']) {
                $pageViews += count($row['time']);
            }
            if (strpos($row['label'], 'Checkout Success Page') !== false) {
                $checkoutCount += count($row['time']);
            }
        }
        fputcsv($fp, ['Checkouts Per Hour:', round($checkoutCount / $executionTime * 3600000, 2)]);
        fputcsv($fp, ['Page Views Per Hour:', round($pageViews / $executionTime * 3600000, 2)]);
        fputcsv($fp, ['Test Duration, s:', round($executionTime / 1000)]);
        fputcsv($fp, ['']);
        fputcsv($fp, ['']);
        fputcsv($fp, $headersArray);
    }
);
