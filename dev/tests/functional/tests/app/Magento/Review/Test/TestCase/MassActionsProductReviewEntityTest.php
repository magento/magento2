<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Simple product created.
 * 2. Product Review created on frontend.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Marketing > User Content > Reviews.
 * 3. Search and select review created in precondition.
 * 4. Select Mass Action.
 * 5. Select Action from Dataset.
 * 6. Click "Submit" button.
 * 7. Perform Asserts.
 *
 * @group Reviews_and_Ratings
 * @ZephyrId MAGETWO-26618
 */
class MassActionsProductReviewEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Backend rating grid page.
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Backend rating edit page.
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Review index page.
     *
     * @var ReviewIndex
     */
    protected $reviewIndex;

    /**
     * Fixture review.
     *
     * @var Review
     */
    protected $review;

    /**
     * Injection data.
     *
     * @param ReviewIndex $reviewIndex
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @param Review $review
     * @return array
     */
    public function __inject(
        ReviewIndex $reviewIndex,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit,
        Review $review
    ) {
        $this->reviewIndex = $reviewIndex;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
        $this->review = $review;
        $this->review->persist();
        $product = $review->getDataFieldConfig('entity_id')['source']->getEntity();

        return ['review' => $this->review, 'product' => $product];
    }

    /**
     * Apply for MassActions ProductReviewEntity.
     *
     * @param string $gridActions
     * @param string $gridStatus
     * @return void
     */
    public function test($gridActions, $gridStatus)
    {
        // Steps
        $this->reviewIndex->open();
        $this->reviewIndex->getReviewGrid()->massaction(
            [['title' => $this->review->getTitle()]],
            [$gridActions => $gridStatus],
            ($gridActions == 'Delete' ? true : false)
        );
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->ratingIndex->open();
        if ($this->review instanceof Review) {
            foreach ($this->review->getRatings() as $rating) {
                $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
                $this->ratingEdit->getPageActions()->delete();
                $this->ratingEdit->getModalBlock()->acceptAlert();
            }
        }
    }
}
