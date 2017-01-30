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
     * Select shipping method.
     *
     * @param array $method
     * @return void
     */
    public function selectShippingMethod(array $method)
    {
        // Code under test uses JavaScript setTimeout at this point as well.
        sleep(3);
        $selector = sprintf($this->shippingMethod, $method['shipping_method'], $method['shipping_service']);
        $this->waitForElementNotVisible($this->blockWaitElement);
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
