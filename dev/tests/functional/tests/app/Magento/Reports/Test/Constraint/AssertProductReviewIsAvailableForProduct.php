<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Reports\Test\Page\Adminhtml\ProductReportReview;
use Magento\Review\Test\Constraint\AssertProductReviewInGrid;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that review is visible in review grid for select product.
 */
class AssertProductReviewIsAvailableForProduct extends AbstractConstraint
{
    /**
     * Assert that review is visible in review grid for select product.
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param ProductReportReview $productReportReview
     * @param AssertProductReviewInGrid $assertProductReviewInGrid
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        ProductReportReview $productReportReview,
        AssertProductReviewInGrid $assertProductReviewInGrid,
        FixtureInterface $product
    ) {
        $productReportReview->open();
        $productReportReview->getGridBlock()->openReview($product->getName());
        unset($assertProductReviewInGrid->filter['visible_in']);
        $filter = $assertProductReviewInGrid->prepareFilter($product, $review->getData(), '');
        $reviewIndex->getReviewGrid()->resetFilter();
        \PHPUnit_Framework_Assert::assertTrue(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Review for ' . $product->getName() . ' product is not visible in reports grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Review is visible in review grid for select product.';
    }
}
