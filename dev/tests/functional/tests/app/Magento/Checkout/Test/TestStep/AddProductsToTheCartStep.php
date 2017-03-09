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
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Adding created products to the cart
 */
class AddProductsToTheCartStep implements TestStepInterface
{
    /**
     * Array with products.
     *
     * @var array
     */
    private $products;

    /**
     * Storefront product view page.
     *
     * @var CatalogProductView
     */
    private $catalogProductView;

    /**
     * Page of checkout page.
     *
     * @var CheckoutCart
     */
    private $checkoutCart;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * Client Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Selector for element wait
     *
     * @var string
     */
    private $loadingSelector = '.loading-mask';

    /**
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     */
    public function __construct(
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        CmsIndex $cmsIndex,
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        array $products
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->checkoutCart = $checkoutCart;
        $this->cmsIndex = $cmsIndex;
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
    }

    /**
     * Add products to the cart.
     *
     * @return array
     */
    public function run()
    {
        // Ensure that shopping cart is empty
        $this->checkoutCart->open()->getCartBlock()->clearShoppingCart();

        foreach ($this->products as $product) {
            $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->waitForElementNotVisible($this->loadingSelector);
            $this->catalogProductView->getViewBlock()->addToCart($product);
            $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        }
        $cart['data']['items'] = ['products' => $this->products];
        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }
}
