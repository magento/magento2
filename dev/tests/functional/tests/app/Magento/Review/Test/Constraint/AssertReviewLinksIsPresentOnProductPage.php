<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Mtf\Client\Browser;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that add and view review links are present on product page.
 */
class AssertReviewLinksIsPresentOnProductPage extends AbstractConstraint
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Assert that add view review links are present on product page.
     *
     * @param Browser $browser
     * @param CatalogProductView $catalogProductView
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(Browser $browser, CatalogProductView $catalogProductView, InjectableFixture $product)
    {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        // Verify add review link
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductView->getReviewSummary()->getAddReviewLink()->isVisible(),
            'Add review link is not visible on product page.'
        );

        // Verify view review link
        $viewReviewLink = $catalogProductView->getReviewSummary()->getViewReviewLink();
        \PHPUnit_Framework_Assert::assertTrue(
            $viewReviewLink->isVisible(),
            'View review link is not visible on product page.'
        );
        \PHPUnit_Framework_Assert::assertContains(
            '1',
            $viewReviewLink->getText(),
            'There is more than 1 approved review.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Add and view review links are present on product page.';
    }
}
