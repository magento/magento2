<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Shipping;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * One page checkout status shipping method block.
 */
class Method extends Block
{
    /**
     * Shipping method selector.
     *
     * @var string
     */
    protected $shippingMethod = './/tbody//tr[td[contains(., "%s")] and td[contains(., "%s")]]//input';

    /**
     * Continue checkout button.
     *
     * @var string
     */
    protected $continue = '#shipping-method-buttons-container button';

    /**
     * Wait element.
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Block wait element.
     *
     * @var string
     */
    protected $blockWaitElement = '._block-content-loading';

    /**
     * Wait until shipping rates will appear.
     *
     * @return void
     */
    private function waitForShippingRates()
    {
        // Code under test uses JavaScript setTimeout at this point as well.
        sleep(3);
        $this->waitForElementNotVisible($this->blockWaitElement);
    }

    /**
     * Retrieve if the shipping methods loader appears.
     *
     * @return bool|null
     */
    public function isLoaderAppeared()
    {
        $this->_rootElement->click();
        return $this->waitForElementVisible($this->waitElement);
    }

    /**
     * Select shipping method.
     *
     * @param array $method
     * @return void
     */
    public function selectShippingMethod(array $method)
    {
        $this->waitForShippingRates();
        $selector = sprintf($this->shippingMethod, $method['shipping_method'], $method['shipping_service']);
        $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Check whether shipping method is available in the shipping rates.
     *
     * @param array $method
     * @return bool
     */
    public function isShippingMethodAvaiable(array $method)
    {
        $this->waitForShippingRates();
        $selector = sprintf($this->shippingMethod, $method['shipping_method'], $method['shipping_service']);
        return $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Click continue button.
     *
     * @return void
     */
    public function clickContinue()
    {
        $this->_rootElement->find($this->continue)->click();
        $browser = $this->browser;
        $selector = $this->waitElement;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }
}
