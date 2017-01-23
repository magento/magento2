<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertPagersSummaryText
 * Assert pagers summary text on checkout/cart/index page
 */
class AssertPagersSummaryText extends AbstractConstraint
{
    /**
     * Verify that pagers summary text on the pagers
     *
     * @param CheckoutCart $checkoutCart
     * @param \Magento\Checkout\Test\Fixture\Cart $cart
     */
    public function processAssert(CheckoutCart $checkoutCart, \Magento\Checkout\Test\Fixture\Cart $cart)
    {
        $checkoutCart->open();
        $pagerSize = 20; //to do add here ability to config on depend of configData $cart
        $totalItems = 0;
        $items = $cart->getItems();
        /** @var  $item */
        foreach ($items as $item) {
            /** @var FixtureInterface $item */
            $checkoutItem = $item->getData();
            $totalItems += $checkoutItem['qty'];
        }
        $checkoutCart->getTopPagerBlock()->getAmountToolbar()->getText();
        \PHPUnit_Framework_Assert::assertSame(
            "Items 1 to $pagerSize of $totalItems total",
            $checkoutCart->getTopPagerBlock()->getAmountToolbar()->getText(),
            'Top Pager summary text isn\'t satisfy test data'
        );
        \PHPUnit_Framework_Assert::assertSame(
            "Items 1 to $pagerSize of $totalItems total",
            $checkoutCart->getBottomPagerBlock()->getAmountToolbar()->getText(),
            'Bottom Pager summary text isn\'t satisfy test data'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Pagers summary text isn\'t satisfy test data' ;
    }
}
