<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Continue to PayPal from one page checkout.
 */
class ContinueToPaypalStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * @construct
     * @param CheckoutOnepage $checkoutOnepage
     */
    public function __construct(CheckoutOnepage $checkoutOnepage)
    {
        $this->checkoutOnepage = $checkoutOnepage;
    }

    /**
     * Click Continue to PayPal button.
     *
     * @return array
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();
    }
}
