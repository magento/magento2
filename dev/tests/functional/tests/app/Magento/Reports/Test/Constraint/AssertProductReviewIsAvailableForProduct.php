<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\ProductReportReview;
use Magento\Review\Test\Constraint\AssertProductReviewInGrid;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewIsVisibleInGrid
 * Assert that review is visible in review grid for select product
 */
class AssertProductReviewIsAvailableForProduct extends AbstractConstraint
{
    /**
     * Assert that review is visible in review grid for select product
     *
     * @param ReviewIndex $reviewIndex
     * @param Review $review
     * @param ProductReportReview $productReportReview
     * @param AssertProductReviewInGrid $assertProductReviewInGrid
     * @return void
     */
    public function processAssert(
        ReviewIndex $reviewIndex,
        Review $review,
        ProductReportReview $productReportReview,
        AssertProductReviewInGrid $assertProductReviewInGrid
    ) {
        $productReportReview->open();
        $product = $review->getDataFieldConfig('entity_id')['source']->getEntity();
        $productReportReview->getGridBlock()->openReview($product->getName());
        unset($assertProductReviewInGrid->filter['visible_in']);
        $filter = $assertProductReviewInGrid->prepareFilter($product, $review->getData(), '');
        \PHPUnit_Framework_Assert::assertTrue(
            $reviewIndex->getReviewGrid()->isRowVisible($filter, false),
            'Review for ' . $product->getName() . ' product is not visible in reports grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Review is visible in review grid for select product.';
    }
}
