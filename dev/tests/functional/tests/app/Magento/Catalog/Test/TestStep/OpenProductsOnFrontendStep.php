<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Mtf\Client\Browser;
use Mtf\TestStep\TestStepInterface;

/**
 * Open products on frontend via url.
 */
class OpenProductsOnFrontendStep implements TestStepInterface
{
    /**
     * Products fixtures.
     *
     * @var array
     */
    protected $products = [];

    /**
     * Browser.
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param array $products
     * @param Browser $browser
     */
    public function __construct(array $products, Browser $browser)
    {
        $this->products = $products;
        $this->browser = $browser;
    }

    /**
     * Open products on frontend via url.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->products as $product) {
            $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        }
    }
}
