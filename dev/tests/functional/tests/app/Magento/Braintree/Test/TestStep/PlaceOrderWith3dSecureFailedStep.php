<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Braintree\Test\Fixture\Secure3dBraintree;

/**
 * Click 'Place order' button and submit 3D secure verification step.
 */
class PlaceOrderWith3dSecureFailedStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * 3D Secure fixture.
     *
     * @var Secure3dBraintree
     */
    private $secure3d;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param Secure3dBraintree $secure3d
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Secure3dBraintree $secure3d
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->secure3d = $secure3d;
    }

    /**
     * Click 'Place order' button and submit 3D secure verification.
     *
     * @return array
     */
    public function run()
    {
        $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();

        $this->checkoutOnepage->getBraintree3dSecureBlock()->fill($this->secure3d);
    }
}
