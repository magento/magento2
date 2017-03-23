<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param array $products
     * @param BrowserInterface $browser
     */
    public function __construct(array $products, BrowserInterface $browser)
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
