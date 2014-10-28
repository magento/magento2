<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
