<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create Product.
 * 2. Create review with rating for this product.
 *
 * Steps:
 * 1. Open Products -> Catalog.
 * 2. Search and open product from preconditions.
 * 3. Open Review tab.
 * 4. Search and open review created in preconditions.
 * 5. Fill data according to dataset.
 * 6. Save changes.
 * 7. Perform all assertions.
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-27743
 */
class UpdateProductReviewEntityOnProductPageTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

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
     * Review fixture.
     *
     * @var Review
     */
    protected $reviewInitial;

    /**
     * Review edit page.
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->reviewInitial = $fixtureFactory->createByCode(
            'review',
            ['dataset' => 'review_for_simple_product_with_rating']
        );
        $this->reviewInitial->persist();
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Injection data.
     *
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @param CatalogProductEdit $catalogProductEdit
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function __inject(
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit,
        CatalogProductEdit $catalogProductEdit,
        ReviewEdit $reviewEdit
    ) {
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->reviewEdit = $reviewEdit;
    }

    /**
     * Update product review on product page.
     *
     * @param Review $review
     * @param int $rating
     * @return array
     */
    public function test(Review $review, $rating)
    {
        // Steps
        $review = $this->createReview($review, $rating);
        $product = $this->reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();
        $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\OpenProductOnBackendStep',
            ['product' => $product]
        )->run();

        $this->catalogProductEdit->getProductForm()->openSection('product_reviews');
        $filter = [
            'title' => $this->reviewInitial->getTitle(),
            'sku' => $product->getSku(),
        ];
        $this->catalogProductEdit->getProductForm()->getSection('product_reviews')->getReviewsGrid()
            ->searchAndOpen($filter);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();
        $productRating = $this->reviewInitial->getDataFieldConfig('ratings')['source']->getRatings()[0];

        return ['product' => $product, 'productRating' => $productRating];
    }

    /**
     * Create review.
     *
     * @param Review $review
     * @param int $rating
     * @return Review
     */
    protected function createReview($review, $rating)
    {
        $reviewData = $review->getData();
        $fixtureRating = $this->reviewInitial->getDataFieldConfig('ratings')['source']->getRatings()[0];
        $reviewData['ratings'][0] = ['fixtureRating' => $fixtureRating, 'rating' => $rating];

        return $this->fixtureFactory->createByCode('review', ['data' => $reviewData]);
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (!$this->reviewInitial instanceof Review) {
            return;
        }
        $this->ratingIndex->open();
        foreach ($this->reviewInitial->getRatings() as $rating) {
            $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
            $this->ratingEdit->getPageActions()->delete();
            $this->ratingEdit->getModalBlock()->acceptAlert();
        }
    }
}
