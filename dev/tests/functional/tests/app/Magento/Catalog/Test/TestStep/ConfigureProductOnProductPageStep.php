<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Configure Product on Product Page step.
 */
class ConfigureProductOnProductPageStep implements TestStepInterface
{
    /**
     * Product fixture.
     *
     * @var InjectableFixture
     */
    private $product;

    /**
     * Frontend product view page.
     *
     * @var CatalogProductView
     */
    private $catalogProductView;

    /**
     * Interface Browser.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * @constructor
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     */
    public function __construct(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        InjectableFixture $product
    ) {
        $this->product = $product;
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
    }

    /**
     * Configure product.
     *
     * @return void
     */
    public function run()
    {
        $this->browser->open($_ENV['app_frontend_url'] . $this->product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->configure($this->product);
    }
}
