<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Reports\Test\Page\Adminhtml\CustomerReportReview;
use Magento\Review\Test\Constraint\AssertProductReviewNotInGrid;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReportByCustomerNotInGrid
 * Check that Customer Product Review not available in grid
 */
class AssertProductReportByCustomerNotInGrid extends AbstractConstraint
{
    /**
     * Asserts Customer Product Review not available in grid
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param AssertProductReviewNotInGrid $assertProductReviewNotInGrid
     * @param CustomerReportReview $customerReportReview
     * @param Customer $customer
     * @param CatalogProductSimple $product
     * @param string $gridStatus
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        AssertProductReviewNotInGrid $assertProductReviewNotInGrid,
        CustomerReportReview $customerReportReview,
        Customer $customer,
        CatalogProductSimple $product,
        $gridStatus = ''
    ) {
        $filter = $assertProductReviewNotInGrid->prepareFilter($product, $review, $gridStatus);

        $customerReportReview->open();
        $customerReportReview->getGridBlock()->openReview($customer);
        $reviewIndex->getReviewGrid()->search($filter);
        unset($filter['visible_in']);
        \PHPUnit_Framework_Assert::assertFalse(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Customer review is present in Review grid.'
        );
    }

    /**
     * Text success if review not in grid on product reviews tab
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer review is absent in grid on product reviews tab.';
    }
}
