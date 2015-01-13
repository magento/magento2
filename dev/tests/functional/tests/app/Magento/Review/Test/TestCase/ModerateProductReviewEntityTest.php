<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for Moderate ProductReview Entity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create product
 * 2. Create product review
 *
 * Steps:
 * 1. Login to backend
 * 2. Open Marketing -> Reviews
 * 3. Search and open review created in precondition
 * 4. Fill data according to dataset
 * 5. Save
 * 6. Perform all assertions
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-26768
 */
class ModerateProductReviewEntityTest extends Injectable
{
    /**
     * Backend review grid page
     *
     * @var ReviewIndex
     */
    protected $reviewIndex;

    /**
     * Backend review edit page
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Injection pages
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function __inject(ReviewIndex $reviewIndex, ReviewEdit $reviewEdit)
    {
        $this->reviewIndex = $reviewIndex;
        $this->reviewEdit = $reviewEdit;
    }

    /**
     * Run moderate product review test
     *
     * @param ReviewInjectable $reviewInitial
     * @param ReviewInjectable $review
     * @return array
     */
    public function test(ReviewInjectable $reviewInitial, ReviewInjectable $review)
    {
        // Precondition
        $reviewInitial->persist();

        // Steps
        $this->reviewIndex->open();
        $this->reviewIndex->getReviewGrid()->searchAndOpen(['review_id' => $reviewInitial->getReviewId()]);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();

        // Prepare data for asserts
        $product = $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();

        return ['product' => $product];
    }
}
