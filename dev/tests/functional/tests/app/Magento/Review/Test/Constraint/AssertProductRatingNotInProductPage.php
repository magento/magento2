<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Fixture\Rating;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingNotInProductPage
 */
class AssertProductRatingNotInProductPage extends AbstractConstraint
{
    /**
     * Assert that product rating is not displayed on frontend on product review
     *
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductSimple $product
     * @param Rating $productRating
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CatalogProductSimple $product,
        Rating $productRating,
        BrowserInterface $browser
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
