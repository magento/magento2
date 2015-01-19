<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\ProductReportView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductViewsReportTotalResult
 * Assert product info in report: product name, price and views
 */
class AssertProductViewsReportTotalResult extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert product info in report: product name, price and views
     *
     * @param ProductReportView $productReportView
     * @param string $total
     * @param array $productsList
     * @return void
     */
    public function processAssert(ProductReportView $productReportView, $total, array $productsList)
    {
        $total = explode(', ', $total);
        $totalForm = $productReportView->getGridBlock()->getViewsResults($productsList);
        \PHPUnit_Framework_Assert::assertEquals($totalForm, $total);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Products view total result is equals to data from dataSet.';
    }
}
