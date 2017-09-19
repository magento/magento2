<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Class CheckoutWithPaypalFromMinicartStep
 */
class CheckoutWithPaypalFromMinicartStep implements TestStepInterface
{
    /**
     * Product fixture.
     *
     * @var FixtureInterface[]
     */
    protected $products;

    /**
     * Catalog product view frontend page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * @constructor
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param array $products
     */
    public function __construct(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        array $products
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        $this->products = $products;
    }

    /**
     * Open product on frontend and click Checkout with PayPal button.
     *
     * @return void
     */
    public function run()
    {
        $product = reset($this->products);
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $parentWindow = $this->catalogProductView->getViewBlock()->braintreePaypalCheckout();
        $this->catalogProductView->getBraintreePaypalBlock()->process($parentWindow);
    }
}
