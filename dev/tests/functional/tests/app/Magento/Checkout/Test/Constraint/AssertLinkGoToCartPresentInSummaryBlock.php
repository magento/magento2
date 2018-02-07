<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutOnepage;

/**
 * Assert that link Go To Cart is present in summary block.
 */
class AssertLinkGoToCartPresentInSummaryBlock extends AbstractConstraint
{
    /**
     * Assert that Go to Cart link is present in checkout summary block.
     *
     * @param CheckoutOnepage $checkoutPage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutPage)
    {
        $reviewBlock = $checkoutPage->getReviewBlock();

        \PHPUnit_Framework_Assert::assertTrue(
            $reviewBlock->getGoToCartLink()->isVisible(),
            'Go to Cart link not present in summary block'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Verify that Go to Cart link is present in checkout summary block';
    }
}
