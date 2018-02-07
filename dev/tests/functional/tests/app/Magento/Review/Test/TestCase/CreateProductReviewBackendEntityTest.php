<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Simple Product created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Marketing > User Content > Reviews.
 * 3. Click the "+" (Add New Review) button.
 * 4. Select the product in the Products Grid.
 * 5. Fill data according to DataSet.
 * 6. Click "Save Review" button.
 * 7. Perform Asserts.
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-26476
 */
class CreateProductReviewBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * ReviewIndex page.
     *
     * @var ReviewIndex
     */
    protected $reviewIndex;

    /**
     * ReviewEdit page.
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * RatingIndex page.
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * RatingEdit page.
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Product rating fixture.
     *
     * @var Rating
     */
    protected $productRating;

    /**
     * Review fixture.
     *
     * @var Review
     */
    protected $review;

    /**
     * Inject pages into test.
     *
     * @param ReviewIndex $reviewIndex
     * @param ReviewEdit $reviewEdit
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(
        ReviewIndex $reviewIndex,
        ReviewEdit $reviewEdit,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit
    ) {
        $this->reviewIndex = $reviewIndex;
        $this->reviewEdit = $reviewEdit;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Run Create Product Review Entity Backend Test.
     *
     * @param Review $review
     * @return array
     */
    public function test(Review $review)
    {
        // Precondition:
        $product = $review->getDataFieldConfig('entity_id')['source']->getEntity();
        $filter = ['id' => $product->getId()];
        $this->review = $review;

        // Steps:
        $this->reviewIndex->open();
        $this->reviewIndex->getReviewActions()->addNew();
        $this->reviewEdit->getProductGrid()->searchAndOpen($filter);
        $this->reviewEdit->getReviewForm()->fill($this->review);
        $this->reviewEdit->getPageActions()->save();

        return ['product' => $product];
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
