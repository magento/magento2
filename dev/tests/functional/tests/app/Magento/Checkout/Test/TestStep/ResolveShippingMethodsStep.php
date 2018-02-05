<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Resolve shipping methods from checkout page.
 */
class ResolveShippingMethodsStep implements TestStepInterface
{
    /**
     * Checkout view page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Open checkout page or not.
     *
     * @var bool
     */
    private $openPage = false;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     * @param bool $openPage
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, $openPage = false)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->openPage = $openPage;
    }

    /**
     * Run step flow.
     *
     * @return array
     */
    public function run()
    {
        if ($this->openPage) {
            $this->checkoutOnepage->open();
        }

        $methods = $this->checkoutOnepage->getShippingMethodBlock()->getAvailableMethods();
        return ['shippingMethods' => $methods];
    }
}
