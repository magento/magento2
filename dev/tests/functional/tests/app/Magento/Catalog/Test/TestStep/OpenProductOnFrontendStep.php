<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Open product on frontend via url
 */
class OpenProductOnFrontendStep implements TestStepInterface
{
    /**
     * Product fixture
     *
     * @var FixtureInterface
     */
    private $product;

    /**
     * Browser
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Preparing step properties
     *
     * @param FixtureInterface $product
     * @param BrowserInterface $browser
     */
    public function __construct(FixtureInterface $product, BrowserInterface $browser)
    {
        $this->product = $product;
        $this->browser = $browser;
    }

    /**
     * Open product on frontend via url
     *
     * @return void
     */
    public function run()
    {
        $this->browser->open($_ENV['app_frontend_url'] . $this->product->getUrlKey() . '.html');
    }
}
