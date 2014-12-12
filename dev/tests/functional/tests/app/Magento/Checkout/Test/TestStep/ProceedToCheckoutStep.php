<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Mtf\TestStep\TestStepInterface;

/**
 * Class ProceedToCheckoutStep
 * Proceed to checkout
 */
class ProceedToCheckoutStep implements TestStepInterface
{
    /**
     * Checkout cart page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * @constructor
     * @param CheckoutCart $checkoutCart
     */
    public function __construct(CheckoutCart $checkoutCart)
    {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Proceed to checkout
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
    }
}
