<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Adding created products to the cart
 */
class AddProductsToTheCartStep implements TestStepInterface
{
    /**
     * Array with products
     *
     * @var array
     */
    protected $products;

    /**
     * Frontend product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Page of checkout page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Interface Browser
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * @constructor
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param array $products
     */
    public function __construct(
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        array $products
    ) {
        $this->products = $products;
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        $this->cmsIndex = $cmsIndex;
        $this->browser = $browser;
    }

    /**
     * Add products to the cart
     *
     * @return void
     */
    public function run()
    {
        // Ensure that shopping cart is empty
        $this->checkoutCart->open()->getCartBlock()->clearShoppingCart();

        foreach ($this->products as $product) {
            $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->addToCart($product);
            $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        }
    }
}
