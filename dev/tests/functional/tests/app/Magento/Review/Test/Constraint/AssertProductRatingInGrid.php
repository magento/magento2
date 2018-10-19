<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingInGrid
 */
class AssertProductRatingInGrid extends AbstractConstraint
{
    /**
     * Assert product Rating availability in product Rating grid
     *
     * @param RatingIndex $ratingIndex
     * @param Rating $productRating
     * @return void
     */
    public function processAssert(RatingIndex $ratingIndex, Rating $productRating)
    {
        $filter = ['rating_code' => $productRating->getRatingCode()];

        $ratingIndex->open();
        \PHPUnit\Framework\Assert::assertTrue(
            $ratingIndex->getRatingGrid()->isRowVisible($filter),
            "Product Rating " . $productRating->getRatingCode() . " is absent on product Rating grid."
        );
    }

    /**
     * Text success exist product Rating in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Rating is present in grid.';
    }
}
