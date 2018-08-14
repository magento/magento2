<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Checkout with PayPal from product page.
 */
class CheckoutWithPaypalFromProductPageStep implements TestStepInterface
{
    /**
     * Product fixture.
     *
     * @var FixtureInterface
     */
    protected $product;

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
     * @param FixtureInterface $product
     * @param BrowserInterface $browser
     */
    public function __construct(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        FixtureInterface $product
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        $this->product = $product;
    }

    /**
     * Open product on frontend and click Checkout with PayPal button.
     *
     * @return void
     */
    public function run()
    {
        $this->browser->open($_ENV['app_frontend_url'] . $this->product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->paypalCheckout();
    }
}
