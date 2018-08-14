<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\DownloadsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertDownloadsReportResult
 * Assert downloads product info in report grid
 */
class AssertDownloadsReportResult extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert product info in report: product name, link title, sku, downloads number (Reports > Products > Downloads)
     *
     * @param OrderInjectable $order
     * @param DownloadsReport $downloadsReport
     * @param int $downloads
     * @return void
     */
    public function processAssert(OrderInjectable $order, DownloadsReport $downloadsReport, $downloads)
    {
        $downloadsReport->open();
        foreach ($order->getEntityId()['products'] as $product) {
            foreach ($product->getDownloadableLinks()['downloadable']['link'] as $link) {
                $filter = [
                    'name' => $product->getName(),
                    'link_title' => $link['title'],
                    'sku' => $product->getSku(),
                ];
                $downloadsReport->getGridBlock()->search($filter);
                $filter[] = $downloads;
                \PHPUnit_Framework_Assert::assertTrue(
                    $downloadsReport->getGridBlock()->isRowVisible($filter, false),
                    "Downloads report link {$link['title']} is not present in reports grid."
                );
            }
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Downloads report is present in reports grid.';
    }
}
