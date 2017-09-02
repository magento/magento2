<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Fixture\Cart;

/**
 * Assert pagers summary text on checkout/cart/index page.
 */
class AssertPagersSummaryText extends AbstractConstraint
{
    const PAGER_SUMMARY_TEXT = "Items 1 to %s of %s total";

    /**
     * Verify that pagers summary text on the shopping cart is correct.
     *
     * @param CheckoutCart $checkoutCart
     * @param \Magento\Checkout\Test\Fixture\Cart $cart
     * @param ConfigData $config
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Cart $cart, ConfigData $config)
    {
        $checkoutCart->open();
        $configSection = $config->getSection();
        $pagerSize = $configSection['checkout/cart/number_items_to_display_pager']['value'];
        $totalItems = count($cart->getItems());

        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::PAGER_SUMMARY_TEXT, $pagerSize, $totalItems),
            $checkoutCart->getTopPagerBlock()->getAmountToolbar()->getText(),
            'Top Pager summary text isn\'t satisfy test data'
        );
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::PAGER_SUMMARY_TEXT, $pagerSize, $totalItems),
            $checkoutCart->getBottomPagerBlock()->getAmountToolbar()->getText(),
            'Bottom Pager summary text isn\'t satisfy test data'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Pagers summary text on the shopping cart is correct.' ;
    }
}
