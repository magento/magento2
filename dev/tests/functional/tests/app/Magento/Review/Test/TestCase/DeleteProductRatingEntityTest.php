<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\TestCase;

use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteProductRatingEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Simple product is created.
 * 2. Product rating is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to Stores->Attributes->Rating.
 * 3. Search product rating in grid by given data.
 * 4. Open this product rating by clicking.
 * 5. Click 'Delete Rating' button.
 * 6. Perform all asserts.
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-23276
 */
class DeleteProductRatingEntityTest extends Injectable
{
    /**
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $product = $fixtureFactory->createByCode('catalogProductSimple', ['dataSet' => 'default']);
        $product->persist();

        return ['product' => $product];
    }

    /**
     * Inject data
     *
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @return void
     */
    public function __inject(RatingIndex $ratingIndex, RatingEdit $ratingEdit)
    {
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
    }

    /**
     * Runs delete product Rating entity test
     *
     * @param Rating $productRating
     * @return void
     */
    public function testDeleteProductRatingEntity(Rating $productRating)
    {
        // Preconditions
        $productRating->persist();

        // Steps
        $this->ratingIndex->open();
        $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $productRating->getRatingCode()]);
        $this->ratingEdit->getPageActions()->delete();
    }
}
