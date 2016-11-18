<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Click 'Place order' button.
 */
class ClickPlaceOrderButtonStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     */
    public function __construct(CheckoutOnepage $checkoutOnepage)
    {
        $this->checkoutOnepage = $checkoutOnepage;
    }

    /**
     * Click 'Place order' button.
     *
     * @return array
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
    }
}
