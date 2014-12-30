<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\CustomerReportReview;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewsQtyByCustomer
 * Check that product reviews qty column in Review Report by Customer grid
 */
class AssertProductReviewsQtyByCustomer extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert product reviews qty column in Review Report by Customer grid
     *
     * @param CustomerReportReview $customerReportReview
     * @param CustomerInjectable $customer
     * @param int $reviewsCount
     * @return void
     */
    public function processAssert(
        CustomerReportReview $customerReportReview,
        CustomerInjectable $customer,
        $reviewsCount
    ) {
        $customerName = $customer->getFirstName() . ' ' . $customer->getLastName();
        $customerReportReview->open();
        \PHPUnit_Framework_Assert::assertEquals(
            $reviewsCount,
            $customerReportReview->getGridBlock()->getQtyReview($customerName),
            'Wrong qty review in Customer Reviews Report grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Product reviews qty column in \'Review Report by Customer\' grid is correct.';
    }
}
