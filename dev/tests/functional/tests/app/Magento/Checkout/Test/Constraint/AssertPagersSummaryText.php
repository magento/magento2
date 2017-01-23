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
     * @param string|null $configData
     */
    public function processAssert(
        CheckoutCart $checkoutCart,
        \Magento\Checkout\Test\Fixture\Cart $cart,
        $configData = null
    ) {
        $checkoutCart->open();
        $pagerSize = 20;
        if ($configData) {
            $configDataArray = $this->objectManager
                    ->get(\Magento\Config\Test\Repository\ConfigData::class)
                    ->get($configData);
            $configDataArrayValue = array_shift($configDataArray);
            $pagerSize = $configDataArrayValue['value'];
        }
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
