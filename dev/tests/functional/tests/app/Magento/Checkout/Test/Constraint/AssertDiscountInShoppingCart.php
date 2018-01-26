<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertDiscountInShoppingCart
 *
 * Assert that discount is equal to expected.
 */
class AssertDiscountInShoppingCart extends AbstractConstraint
{
    /**
     * Assert that discount is equal to expected.
     *
     * @param Customer $customer
     * @param CheckoutCart $checkoutCart
     * @param Cart $cart
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CheckoutCart $checkoutCart,
        Cart $cart
    ) {
        $loginStep = $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        );
        $loginStep->run();
        $checkoutCart->open();
        $checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
        \PHPUnit_Framework_Assert::assertEquals(
            number_format($cart->getDiscount(), 2),
            $checkoutCart->getTotalsBlock()->getDiscount(),
            'Discount amount in the shopping cart not equals to discount amount from fixture.'
        );
        $loginStep->cleanUp();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Discount in the shopping cart equals to expected discount amount from data set.';
    }
}
