<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Review\Test\Fixture\Review;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewNotOnProductPage
 * Assert that product review Not available on product page
 */
class AssertProductReviewNotOnProductPage extends AbstractConstraint
{
    /**
     * Assert that product review Not available on product page
     *
     * @param CatalogProductView $catalogProductView
     * @param Review $reviewInitial
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        Review $reviewInitial,
        BrowserInterface $browser
    ) {
        /** @var CatalogProductSimple $product */
        $product = $reviewInitial->getDataFieldConfig('entity_id')['source']->getEntity();
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        $reviewBlock = $catalogProductView->getCustomerReviewBlock();
        $catalogProductView->getViewBlock()->selectTab('Reviews');
        \PHPUnit_Framework_Assert::assertFalse(
            $reviewBlock->isVisibleReviewItem(),
            'Error, product review is displayed.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Review is not available on the product page.';
    }
}
