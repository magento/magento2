<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\TestCase;

use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Review\Test\Page\Adminhtml\RatingNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create simple product.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate Stores > Attributes > Rating.
 * 3. Add New Rating.
 * 4. Fill data according to dataset.
 * 5. Save Rating.
 * 6. Perform asserts.
 *
 * @group Reviews_and_Ratings
 * @ZephyrId MAGETWO-23331
 */
class CreateProductRatingEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Product rating fixture.
     *
     * @var Rating
     */
    protected $productRating;

    /**
     * Product rating grid page.
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Create product rating page.
     *
     * @var RatingNew
     */
    protected $ratingNew;

    /**
     * Edit product rating page.
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Prepare data.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $product = $fixtureFactory->createByCode('catalogProductSimple', ['dataset' => 'default']);
        $product->persist();

        return ['product' => $product];
    }

    /**
     * Injection data.
     *
     * @param RatingIndex $ratingIndex
     * @param RatingNew $ratingNew
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(
        RatingIndex $ratingIndex,
        RatingNew $ratingNew,
        RatingEdit $ratingEdit
    ) {
        $this->ratingIndex = $ratingIndex;
        $this->ratingNew = $ratingNew;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Run create backend Product Rating test.
     *
     * @param Rating $productRating
     * @return void
     */
    public function testCreateProductRatingEntityTest(Rating $productRating)
    {
        // Prepare data for tear down
        $this->productRating = $productRating;

        // Steps
        $this->ratingIndex->open();
        $this->ratingIndex->getGridPageActions()->addNew();
        $this->ratingNew->getRatingForm()->fill($productRating);
        $this->ratingNew->getPageActions()->save();
    }

    /**
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (!($this->productRating instanceof Rating)) {
            return;
        }
        $filter = ['rating_code' => $this->productRating->getRatingCode()];
        $this->ratingIndex->open();
        $this->ratingIndex->getRatingGrid()->searchAndOpen($filter);
        $this->ratingEdit->getPageActions()->delete();
        $this->ratingEdit->getModalBlock()->acceptAlert();
    }
}
