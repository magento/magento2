<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create simple product
 * 2. Create review for this product
 *
 * Test Flow:
 * 1. Login as admin
 * 2. Navigate to the Reports>Reviews>By Products
 * 3. Perform appropriate assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27223
 */
class ProductReviewReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Creation product review report entity
     *
     * @param Review $review
     * @return void
     */
    public function test(Review $review)
    {
        // Preconditions
        $review->persist();
    }
}
