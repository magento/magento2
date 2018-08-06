<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * @param CheckoutCart $checkoutCart
     * @param CmsIndex $cmsIndex
     */
    public function __construct(
        CheckoutCart $checkoutCart,
        CmsIndex $cmsIndex
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Checkout with PayPal from Shopping Cart.
     *
     * @return void
     */
    public function run()
    {
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->waitCartContainerLoading();
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->checkoutCart->getCartBlock()->inContextPaypalCheckout();
    }
}
