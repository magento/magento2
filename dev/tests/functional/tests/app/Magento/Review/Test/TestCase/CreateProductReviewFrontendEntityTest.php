<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Constraint\AssertProductReviewIsAbsentOnProductPage;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create simple product.
 * 2. Create custom rating type.
 *
 * Steps:
 * 1. Open frontend.
 * 2. Go to product page.
 * 3. Click "Be the first to review this product".
 * 4. Fill data according to dataset.
 * 5. click "Submit review".
 * 6. Perform all assertions.
 *
 * @group Reviews_and_Ratings
 * @ZephyrId MAGETWO-25519
 */
class CreateProductReviewFrontendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    /* end tags */

    /**
     * Frontend product view page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

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
     * Fixture review.
     *
     * @var Review
     */
    protected $review;

    /**
     * Injection data.
     *
     * @param CatalogProductView $catalogProductView
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(
        CatalogProductView $catalogProductView,
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Run create frontend product rating test.
     *
     * @param Review $review
     * @param BrowserInterface $browser
     * @param AssertProductReviewIsAbsentOnProductPage $assertProductReviewIsAbsentOnProductPage
     * @return array
     */
    public function test(
        Review $review,
        BrowserInterface $browser,
        AssertProductReviewIsAbsentOnProductPage $assertProductReviewIsAbsentOnProductPage
    ) {
        // Prepare for tear down
        $this->review = $review;

        // Steps
        $product = $review->getDataFieldConfig('entity_id')['source']->getEntity();
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $assertProductReviewIsAbsentOnProductPage->processAssert($this->catalogProductView);
        $this->catalogProductView->getReviewSummary()->clickAddReviewLink();
        $reviewForm = $this->catalogProductView->getReviewFormBlock();
        $reviewForm->fill($review);
        $reviewForm->submit();

        return ['product' => $product];
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->review instanceof Review) {
            $ratings = $this->review->getRatings();
            if (empty($ratings)) {
                return;
            }
            $this->ratingIndex->open();
            foreach ($ratings as $rating) {
                $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
                $this->ratingEdit->getPageActions()->delete();
                $this->ratingEdit->getModalBlock()->acceptAlert();
            }
        }
    }
}
