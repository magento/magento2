<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Review\Test\TestCase;

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewEdit;
use Magento\Review\Test\Page\Adminhtml\RatingEdit;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Test Creation for UpdateProductReviewEntity on product page
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Product
 * 2. Create review with rating for this product
 *
 * Steps:
 * 1. Open Products -> Catalog
 * 2. Search and open product from preconditions
 * 3. Open Review tab
 * 4. Search and open review created in preconditions
 * 5. Fill data according to dataSet
 * 6. Save changes
 * 7. Perform all assertions
 *
 * @group Reviews_and_Ratings_(MX)
 * @ZephyrId MAGETWO-27743
 */
class UpdateProductReviewEntityOnProductPageTest extends Injectable
{
    /**
     * Catalog product index page
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog product edit page
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Backend rating grid page
     *
     * @var RatingIndex
     */
    protected $ratingIndex;

    /**
     * Backend rating edit page
     *
     * @var RatingEdit
     */
    protected $ratingEdit;

    /**
     * Review fixture
     *
     * @var ReviewInjectable
     */
    protected $reviewInitial;

    /**
     * Review edit page
     *
     * @var ReviewEdit
     */
    protected $reviewEdit;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepare data
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->reviewInitial = $fixtureFactory->createByCode(
            'reviewInjectable',
            ['dataSet' => 'review_for_simple_product_with_rating']
        );
        $this->reviewInitial->persist();
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Injection data
     *
     * @param RatingIndex $ratingIndex
     * @param RatingEdit $ratingEdit
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param ReviewEdit $reviewEdit
     * @return void
     */
    public function __inject(
        RatingIndex $ratingIndex,
        RatingEdit $ratingEdit,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        ReviewEdit $reviewEdit
    ) {
        $this->ratingIndex = $ratingIndex;
        $this->ratingEdit = $ratingEdit;
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->reviewEdit = $reviewEdit;
    }

    /**
     * Update product review on product page
     *
     * @param ReviewInjectable $review
     * @param int $rating
     * @return array
     */
    public function test(ReviewInjectable $review, $rating)
    {
        // Steps
        $review = $this->createReview($review, $rating);
        $this->catalogProductIndex->open();
        /** @var CatalogProductSimple $product */
        $product = $this->reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $product->getSku()]);
        $this->catalogProductEdit->getForm()->openTab('product_reviews');
        $filter = [
            'title' => $this->reviewInitial->getTitle(),
            'sku' => $product->getSku()
        ];
        $this->catalogProductEdit->getForm()->getTabElement('product_reviews')->getReviewsGrid()
            ->searchAndOpen($filter);
        $this->reviewEdit->getReviewForm()->fill($review);
        $this->reviewEdit->getPageActions()->save();
        $productRating = $this->reviewInitial->getDataFieldConfig('ratings')['source']->getRatings()[0];

        return ['product' => $product, 'productRating' => $productRating];
    }

    /**
     * Create review
     *
     * @param ReviewInjectable $review
     * @param int $rating
     * @return ReviewInjectable
     */
    protected function createReview($review, $rating)
    {
        $reviewData = $review->getData();
        $fixtureRating = $this->reviewInitial->getDataFieldConfig('ratings')['source']->getRatings()[0];
        $reviewData['ratings'][0] = ['fixtureRating' => $fixtureRating, 'rating' => $rating];

        return $this->fixtureFactory->createByCode('reviewInjectable', ['data' => $reviewData]);
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        if (!$this->reviewInitial instanceof ReviewInjectable) {
            return;
        }
        $this->ratingIndex->open();
        foreach ($this->reviewInitial->getRatings() as $rating) {
            $this->ratingIndex->getRatingGrid()->searchAndOpen(['rating_code' => $rating['title']]);
            $this->ratingEdit->getPageActions()->delete();
        }
    }
}
