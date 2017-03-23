<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Fixture\Rating;
use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingInProductPage
 * Assert that product rating is displayed on product review(frontend)
 */
class AssertProductRatingInProductPage extends AbstractConstraint
{
    /**
     * Assert that product rating is displayed on product review(frontend)
     *
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $product
     * @param Review|null $review [optional]
     * @param Rating|null $productRating [optional]
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        CatalogProductSimple $product,
        Review $review = null,
        Rating $productRating = null
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $reviewSummaryBlock = $catalogProductView->getReviewSummary();
        if ($reviewSummaryBlock->isVisible()) {
            $reviewSummaryBlock->getAddReviewLink()->click();
        }
        $rating = $productRating ? $productRating : $review->getDataFieldConfig('ratings')['source']->getRatings()[0];
        $reviewForm = $catalogProductView->getReviewFormBlock();
        \PHPUnit_Framework_Assert::assertTrue(
            $reviewForm->isVisibleRating($rating),
            'Product rating "' . $rating->getRatingCode() . '" is not displayed.'
        );
    }

    /**
     * Text success product rating is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Product rating is displayed.';
    }
}
