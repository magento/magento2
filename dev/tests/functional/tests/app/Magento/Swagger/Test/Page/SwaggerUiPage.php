<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Class SwaggerUiPage
 */
class SwaggerUiPage
{
    /**
     * Swagger mca
     */
    const MCA = 'swagger/';

    /**
     * Page url
     *
     * @var string
     */
    protected $url;

    /**
     * Client Browser
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Selector for title
     *
     * @var string
     */
    protected $titleSelector = '.info_title';

    /**
     * Constructor
     * Set client browser and page url
     *
     * @constructor
     * @param BrowserInterface $browser
     */
    public function __construct(BrowserInterface $browser)
    {
        $this->browser = $browser;
        $this->url = $_ENV['app_frontend_url'] . self::MCA;
    }

    /**
     * Open page through browser
     *
     * @return void
     */
    public function open()
    {
        $this->openSwaggerUrl();
        $this->waitForPageToLoad();
    }

    /**
     * Close page through browser
     *
     * @return void
     */
    public function close()
    {
        $this->browser->closeWindow();
    }

    /**
     * Check if element is visible
     *
     * @param string $selector
     * @param string $strategy
     * @return bool
     */
    public function isElementVisible($selector, $strategy = Locator::SELECTOR_CSS)
    {
        return $this->browser->find($selector, $strategy)->isVisible();
    }

    /**
     * Expand service content
     *
     * @param string $serviceName
     * @return void
     */
    public function expandServiceContent($serviceName)
    {
        /**
         * Selector for service
         */
        $serviceSelector = 'a#endpointListTogger_%s';
        /**
         * Selector for endpoint
         */
        $endpointSelector = 'ul#%s_endpoint_list';
        $serviceSelector = sprintf($serviceSelector, $serviceName);
        $endpointSelector = sprintf($endpointSelector, $serviceName);
        if (!$this->isElementVisible($endpointSelector)) {
            $this->browser->find($serviceSelector, Locator::SELECTOR_CSS)->click();
        }
    }

    /**
     * Close service content
     *
     * @param string $serviceName
     * @return void
     */
    public function closeServiceContent($serviceName)
    {
        /**
         * Selector for service
         */
        $serviceSelector = 'a#endpointListTogger_%s';
        /**
         * Selector for endpoint
         */
        $endpointSelector = 'ul#%s_endpoint_list';
        $serviceSelector = sprintf($serviceSelector, $serviceName);
        $endpointSelector = sprintf($endpointSelector, $serviceName);
        if ($this->isElementVisible($endpointSelector)) {
            $this->browser->find($serviceSelector, Locator::SELECTOR_CSS)->click();
        }
    }

    /**
     * Expand endpoint content
     *
     * @param string $serviceName
     * @param string $endpoint
     * @return void
     */
    public function expandEndpointContent($serviceName, $endpoint)
    {
        $this->expandServiceContent($serviceName);
        /**
         * Selector for endpoint href
         */
        $endpointRefSelector = 'a[href$="%s%s"]';
        /**
         * Selector for operation
         */
        $operationSelector = 'div[id$="%s%s_content"]';
        $endpointRefSelector = sprintf($endpointRefSelector, $serviceName, $endpoint);
        $operationSelector = sprintf($operationSelector, $serviceName, $endpoint);
        if (!$this->isElementVisible($operationSelector)) {
            $this->browser->find($endpointRefSelector, Locator::SELECTOR_CSS)->click();
        }
    }

    /**
     * Close endpoint content
     *
     * @param string $serviceName
     * @param string $endpoint
     * @return void
     */
    public function closeEndpointContent($serviceName, $endpoint)
    {
        $this->expandServiceContent($serviceName);
        /**
         * Selector for endpoint href
         */
        $endpointRefSelector = 'a[href$="%s%s"]';
        /**
         * Selector for operation
         */
        $operationSelector = 'div[id$="%s%s_content"]';
        $endpointRefSelector = sprintf($endpointRefSelector, $serviceName, $endpoint);
        $operationSelector = sprintf($operationSelector, $serviceName, $endpoint);
        if ($this->isElementVisible($operationSelector)) {
            $this->browser->find($endpointRefSelector, Locator::SELECTOR_CSS)->click();
        }
    }

    /**
     * Wait for page to load
     *
     * @return void
     */
    private function waitForPageToLoad()
    {
        $this->waitForElementVisible($this->titleSelector);
    }

    /**
     * Wait for element to be visible
     *
     * @param string $selector
     * @param string $strategy
     * @return bool|null
     */
    private function waitForElementVisible($selector, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;
        return $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                $element = $browser->find($selector, $strategy);
                return $element->isVisible() ? true : null;
            }
        );
    }

    /**
     * Wait to open swagger url
     *
     * This is to work around an issue with selenium web driver randomly returns browser url as "about:blank"
     * when open swagger page
     *
     * @return bool|null
     */
    private function openSwaggerUrl()
    {
        $browser = $this->browser;
        $pattern = self::MCA;
        return $browser->waitUntil(
            function () use ($browser, $pattern) {
                try {
                    $url = $_ENV['app_frontend_url'] . $pattern;
                    $browser->open($url);
                    return true;
                } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                    return false;
                }
            }
        );
    }
}
