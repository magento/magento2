<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Paypal\Test\Constraint\AssertExpressSuccessfullyCancelledMessage;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Paypal\Test\Block\Sandbox\ExpressLogin;

/**
 * Checkout with PayPal from Shopping Cart.
 */
class InContextCheckoutWithPaypalFromShoppingCartStep implements TestStepInterface
{
    /**
     * Shopping Cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @var AssertExpressSuccessfullyCancelledMessage
     */
    private $assertExpressSuccessfullyCancelledMessage;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     * @param AssertExpressSuccessfullyCancelledMessage $assertExpressSuccessfullyCancelledMessage
     */
    public function __construct(
        CheckoutCart $checkoutCart,
        AssertExpressSuccessfullyCancelledMessage $assertExpressSuccessfullyCancelledMessage
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->assertExpressSuccessfullyCancelledMessage = $assertExpressSuccessfullyCancelledMessage;
    }

    /**
     * Checkout with PayPal from Shopping Cart.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->inContextPaypalCheckout();
        $this->assertExpressSuccessfullyCancelledMessage->processAssert($this->checkoutCart);
    }
}
