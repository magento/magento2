<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Fixture\Rating;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingNotInProductPage
 */
class AssertProductRatingNotInProductPage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that product rating is not displayed on frontend on product review
     *
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductSimple $product
     * @param Rating $productRating
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CatalogProductSimple $product,
        Rating $productRating,
        Browser $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $catalogProductView->getReviewSummary()->getAddReviewLink()->click();

        $reviewForm = $catalogProductView->getReviewFormBlock();
        \PHPUnit_Framework_Assert::assertFalse(
            $reviewForm->isVisibleRating($productRating),
            'Product rating "' . $productRating->getRatingCode() . '" is displayed.'
        );
    }

    /**
     * Text success product rating is not displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Product rating is not displayed.';
    }
}
