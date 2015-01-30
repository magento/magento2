<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Shipping;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Method
 * One page checkout status shipping method block
 *
 */
class Method extends Block
{
    /**
     * Shipping method selector
     *
     * @var string
     */
    protected $shippingMethod = '//dt[text()="%s"]/following-sibling::*//label/span[contains(text(), "%s")]';

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
     * Select shipping method
     *
     * @param array $method
     * @return void
     */
    public function selectShippingMethod(array $method)
    {
        $selector = sprintf($this->shippingMethod, $method['shipping_service'], $method['shipping_method']);
        $this->waitForElementVisible($selector, Locator::SELECTOR_XPATH);
        $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Click continue button
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
