<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Mtf\TestStep\TestStepInterface;

/**
 * Class FillShippingMethodStep
 * Fill shipping information
 */
class FillShippingMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Shipping carrier and method
     *
     * @var array
     */
    protected $shipping;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $shipping
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, array $shipping)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->shipping = $shipping;
    }

    /**
     * Select shipping method
     *
     * @return void
     */
    public function run()
    {
        if ($this->shipping['shipping_service'] !== '-') {
            $this->checkoutOnepage->getShippingMethodBlock()->selectShippingMethod($this->shipping);
            $this->checkoutOnepage->getShippingMethodBlock()->clickContinue();
        }
    }
}
