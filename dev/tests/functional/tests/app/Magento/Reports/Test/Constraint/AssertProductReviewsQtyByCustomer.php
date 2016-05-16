<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Reports\Test\Page\Adminhtml\CustomerReportReview;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewsQtyByCustomer
 * Check that product reviews qty column in Review Report by Customer grid
 */
class AssertProductReviewsQtyByCustomer extends AbstractConstraint
{
    /**
     * Assert product reviews qty column in Review Report by Customer grid
     *
     * @param CustomerReportReview $customerReportReview
     * @param Customer $customer
     * @param int $reviewsCount
     * @return void
     */
    public function processAssert(
        CustomerReportReview $customerReportReview,
        Customer $customer,
        $reviewsCount
    ) {
        $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
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
