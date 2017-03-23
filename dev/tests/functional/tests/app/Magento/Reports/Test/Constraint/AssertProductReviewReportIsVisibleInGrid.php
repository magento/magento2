<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\ProductReportReview;
use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewReportIsVisibleInGrid
 * Assert that Product Review Report is visible in reports grid
 */
class AssertProductReviewReportIsVisibleInGrid extends AbstractConstraint
{
    /**
     * Assert that Product Review Report is visible in reports grid
     *
     * @param ProductReportReview $productReportReview
     * @param Review $review
     * @return void
     */
    public function processAssert(ProductReportReview $productReportReview, Review $review)
    {
        $productReportReview->open();
        $name = $review->getDataFieldConfig('entity_id')['source']->getEntity()->getName();
        \PHPUnit_Framework_Assert::assertTrue(
            $productReportReview->getGridBlock()->isRowVisible(['title' => $name], false),
            'Review for ' . $name . ' product is not visible in reports grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Review Report is visible in reports grid.';
    }
}
