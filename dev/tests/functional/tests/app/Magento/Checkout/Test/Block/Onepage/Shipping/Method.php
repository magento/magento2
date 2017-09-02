<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Shipping;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * One page checkout status shipping method block
 */
class Method extends Block
{
    /**
     * Shipping method selector
     *
     * @var string
     */
    protected $shippingMethod = './/tbody//tr[td[contains(., "%s")] and td[contains(., "%s")]]//input';

    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#shipping-method-buttons-container button';

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Block wait element
     *
     * @var string
     */
    protected $blockWaitElement = '._block-content-loading';

    /**
     * Shipping method row locator.
     *
     * @var string
     */
    private $shippingMethodRow = '#checkout-step-shipping_method tbody tr.row';

    /**
     * Shipping error row locator.
     *
     * @var string
     */
    private $shippingError = '#checkout-step-shipping_method tbody tr.row-error';

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
     * Click continue button.
     *
     * @return void
     */
    public function clickContinue()
    {
        $this->waitForShippingRates();
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

    /**
     * Wait until shipping rates will appear.
     *
     * @return void
     */
    public function waitForShippingRates()
    {
        // Code under test uses JavaScript setTimeout at this point as well.
        sleep(3);
        $this->waitForElementNotVisible($this->blockWaitElement);
    }

    /**
     * Return available shipping methods with prices.
     *
     * @return array
     */
    public function getAvailableMethods()
    {
        $this->waitForShippingRates();
        $methods = $this->_rootElement->getElements($this->shippingMethodRow);
        $result = [];
        foreach ($methods as $method) {
            $methodName = trim($method->find('td.col-method:not(:first-child)')->getText());
            $methodPrice = trim($method->find('td.col-price')->getText());
            $methodPrice = $this->escapeCurrency($methodPrice);

            $result[$methodName] = $methodPrice;
        }

        return $result;
    }

    /**
     * Is shipping rates estimation error present.
     *
     * @return bool
     */
    public function isErrorPresent()
    {
        $this->waitForShippingRates();

        return $this->_rootElement->find($this->shippingError)->isVisible();
    }

    /**
     * Escape currency in price.
     *
     * @param string $price
     * @return string|null
     */
    private function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);

        return (isset($matches[1])) ? $matches[1] : null;
    }
}
