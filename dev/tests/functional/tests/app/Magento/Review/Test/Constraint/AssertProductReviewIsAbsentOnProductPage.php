<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert that product don't have a review on product page.
 */
class AssertProductReviewIsAbsentOnProductPage extends AbstractConstraint
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Verify message for assert.
     */
    const NO_REVIEW_LINK_TEXT = 'Be the first to review this product';

    /**
     * Assert that product doesn't have a review on product page.
     *
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(CatalogProductView $catalogProductView)
    {
        $catalogProductView->getViewBlock()->selectTab('Reviews');

        \PHPUnit_Framework_Assert::assertFalse(
            $catalogProductView->getCustomerReviewBlock()->isVisibleReviewItem(),
            'No reviews below the form required.'
        );

        \PHPUnit_Framework_Assert::assertEquals(
            self::NO_REVIEW_LINK_TEXT,
            trim($catalogProductView->getReviewSummary()->getAddReviewLink()->getText()),
            sprintf('"%s" link is not available', self::NO_REVIEW_LINK_TEXT)
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product do not have a review on product page.';
    }
}
