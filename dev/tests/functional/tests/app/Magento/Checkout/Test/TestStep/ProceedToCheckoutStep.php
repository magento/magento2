<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * @param CheckoutCart $checkoutCart
     * @param CmsIndex $cmsIndex
     */
    public function __construct(CheckoutCart $checkoutCart, CmsIndex $cmsIndex)
    {
        $this->checkoutCart = $checkoutCart;
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Proceed to checkout
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->waitCartContainerLoading();
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->checkoutCart->getProceedToCheckoutBlock()->proceedToCheckout();
    }
}
