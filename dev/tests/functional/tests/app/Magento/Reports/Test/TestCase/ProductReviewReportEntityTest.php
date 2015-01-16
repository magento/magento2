<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Review\Test\Fixture\ReviewInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for ProductReviewReportEntity
 *
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
    /**
     * Creation product review report entity
     *
     * @param ReviewInjectable $review
     * @return void
     */
    public function test(ReviewInjectable $review)
    {
        // Preconditions
        $review->persist();
    }
}
