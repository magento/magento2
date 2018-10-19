<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Reports\Test\Page\Adminhtml\CustomerReportReview;
use Magento\Review\Test\Constraint\AssertProductReviewInGrid;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReportByCustomerInGrid
 * Check that Customer review is displayed in grid
 */
class AssertProductReportByCustomerInGrid extends AbstractConstraint
{
    /**
     * Assert that Customer review is displayed in grid
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param AssertProductReviewInGrid $assertProductReviewInGrid
     * @param CustomerReportReview $customerReportReview
     * @param Customer $customer
     * @param CatalogProductSimple $product
     * @param string $gridStatus
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        AssertProductReviewInGrid $assertProductReviewInGrid,
        CustomerReportReview $customerReportReview,
        Customer $customer,
        CatalogProductSimple $product = null,
        $gridStatus = ''
    ) {
        $filter = $assertProductReviewInGrid->prepareFilter($product, $review->getData(), $gridStatus);

        $customerReportReview->open();
        $customerReportReview->getGridBlock()->openReview($customer);
        $reviewIndex->getReviewGrid()->search($filter);
        unset($filter['visible_in']);
        \PHPUnit\Framework\Assert::assertTrue(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Customer review is absent in Review grid.'
        );
    }

    /**
     * Text success exist review in grid on product reviews tab
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer review is present in grid on product reviews tab.';
    }
}
