<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shipping method is or isn't available as expected.
 */
class AssertShippingMethodAvailableInCart extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that shipping method is or isn't available as expected.
     *
     * @param CheckoutCart $checkoutCart
     * @param array $shippingExists
     * @param boolean $requireReload
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, array $shippingExists, $requireReload = true)
    {
        if ($requireReload) {
            $checkoutCart->open();
        }

        \PHPUnit\Framework\Assert::assertEquals(
            $checkoutCart->getShippingBlock()->isShippingCarrierMethodVisible(
                $shippingExists['shipping_service'],
                $shippingExists['shipping_method']
            ),
            $shippingExists['exists']
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipping method in the shopping cart is or is not available as expected.';
    }
}
