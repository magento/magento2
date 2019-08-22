<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Set the shipping method on the estimate page
 */
class FillShippingMethodOnEstimateStep implements TestStepInterface
{
    /**
     * Page of checkout page.
     *
     * @var CheckoutCart
     */
    private $checkoutCart;

    /**
     * Customer Address.
     *
     * @var Address
     */
    private $address;

    /**
     * Shipping method title and shipping service name.
     *
     * @var array
     */
    private $shipping;

    /**
     * @param CheckoutCart $checkoutCart
     * @param Address $address
     * @param array $shipping
     */
    public function __construct(
        CheckoutCart $checkoutCart,
        Address $address,
        array $shipping = []
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->address = $address;
        $this->shipping = $shipping;
    }

    /**
     * Load shipping information and set the shipping method.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->waitCartContainerLoading();
        $this->checkoutCart->getShippingBlock()->resetAddress();
        $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($this->address);
        if (!empty($this->shipping)) {
            $this->checkoutCart->getShippingBlock()->selectShippingMethod($this->shipping);
        }
        $this->checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
    }
}
