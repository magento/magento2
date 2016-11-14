<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Assert that shopping cart data is correct for several customers.
 */
class AssertCartPerCustomer extends AbstractConstraint
{
    /**
     *  Welcome message pattern.
     */
    const WELCOME_MESSAGE = 'Welcome, %s!';

    /**
     * Assert shopping cart data for each customer.
     *
     * @param CheckoutCart $checkoutCart
     * @param CmsIndex $cmsIndex
     * @param TestStepFactory $stepFactory
     * @param array $customers
     * @param array $cartFixtures
     * @param AssertProductQtyInShoppingCart $assertProductQty
     * @param AssertSubtotalInShoppingCart $assertSubtotal
     * @param AssertGrandTotalInShoppingCart $assertGrandtotal
     * @param AssertProductDataInMiniShoppingCart $assertMinicart
     * @return void
     */
    public function processAssert(
        CheckoutCart $checkoutCart,
        CmsIndex $cmsIndex,
        TestStepFactory $stepFactory,
        array $customers,
        array $cartFixtures,
        AssertProductQtyInShoppingCart $assertProductQty,
        AssertSubtotalInShoppingCart $assertSubtotal,
        AssertGrandTotalInShoppingCart $assertGrandtotal,
        AssertProductDataInMiniShoppingCart $assertMinicart
    ) {
        foreach ($customers as $index => $customer) {
            if (!empty($cartFixtures[$index])) {
                $stepFactory->create(
                    \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
                    ['customer' => $customer]
                )->run();
                \PHPUnit_Framework_Assert::assertEquals(
                    sprintf(self::WELCOME_MESSAGE, $customer->getFirstname()),
                    $cmsIndex->getLinksBlock()->getWelcomeText(),
                    'Customer welcome message is wrong.'
                );
                $assertProductQty->processAssert($checkoutCart, $cartFixtures[$index]);
                $assertSubtotal->processAssert($checkoutCart, $cartFixtures[$index]);
                $assertGrandtotal->processAssert($checkoutCart, $cartFixtures[$index]);
                $assertMinicart->processAssert($cmsIndex, $cartFixtures[$index]);
            }
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Shopping cart data is correct for each customer.';
    }
}
