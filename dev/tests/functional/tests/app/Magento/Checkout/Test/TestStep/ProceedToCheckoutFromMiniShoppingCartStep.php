<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Proceed to checkout from mini shopping cart.
 */
class ProceedToCheckoutFromMiniShoppingCartStep implements TestStepInterface
{
    /**
     * Mini shopping cart block
     *
     * @var \Magento\Checkout\Test\Block\Cart\Sidebar
     */
    protected $miniShoppingCart;

    /**
     * @param CmsIndex $cmsIndex
     */
    public function __construct(CmsIndex $cmsIndex)
    {
        $this->miniShoppingCart = $cmsIndex->getCartSidebarBlock();
    }

    /**
     * Proceed to checkout
     *
     * @return void
     */
    public function run()
    {
        $this->miniShoppingCart->openMiniCart();
        $this->miniShoppingCart->clickProceedToCheckoutButton();
    }
}
