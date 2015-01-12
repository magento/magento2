<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderGrandTotal
 * Assert that Order Grand Total is correct on order page in backend
 */
class AssertOrderGrandTotal extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that Order Grand Total is correct on order page in backend
     *
     * @param OrderView $salesOrderView
     * @param string $grandTotal
     * @return void
     */
    public function processAssert(OrderView $salesOrderView, $grandTotal)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $grandTotal,
            $salesOrderView->getOrderTotalsBlock()->getGrandTotal(),
            'Grand Total price does not equal to price from data set.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand Total price equals to price from data set.';
    }
}
