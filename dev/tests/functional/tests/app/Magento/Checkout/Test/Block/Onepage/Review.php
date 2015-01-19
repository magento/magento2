<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Mtf\Block\Block;
use Mtf\Block\BlockFactory;
use Mtf\Client\Driver\Selenium\Browser;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Review
 * One page checkout status review block
 *
 */
class Review extends Block
{
    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#review-buttons-container button';

    /**
     * Centinel authentication block
     *
     * @var string
     */
    protected $centinelBlock = '#centinel-authenticate-block';

    /**
     * Grand total search mask
     *
     * @var string
     */
    protected $grandTotal = '//tr[@class="grand totals"]/td[@class="amount"]//span';

    /**
     * Subtotal search mask
     *
     * @var string
     */
    protected $subtotal = '//tr[@class="totals sub"]/td[@class="amount"]//span';

    /**
     * Tax search mask
     *
     * @var string
     */
    protected $tax = '//tr[@class="totals-tax"]/td[@class="amount"]//span';

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * @constructor
     * @param Element $element
     * @param BlockFactory $blockFactory
     * @param Browser $browser
     */
    public function __construct(Element $element, BlockFactory $blockFactory, Browser $browser)
    {
        parent::__construct($element, $blockFactory, $browser);
        $this->browser->switchToFrame();
    }
    /**
     * Fill billing address
     *
     * @return void
     */
    public function placeOrder()
    {
        $this->_rootElement->find($this->continue, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Wait for 3D Secure card validation
     *
     * @return void
     */
    public function waitForCardValidation()
    {
        $this->waitForElementNotVisible($this->centinelBlock);
    }

    /**
     * Get Grand Total Text
     *
     * @return array|string
     */
    public function getGrandTotal()
    {
        $grandTotal = $this->_rootElement->find($this->grandTotal, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($grandTotal);
    }

    /**
     * Get Tax text from Order Totals
     *
     * @return array|string
     */
    public function getTax()
    {
        $tax = $this->_rootElement->find($this->tax, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($tax);
    }

    /**
     * Get Subtotal text
     *
     * @return array|string
     */
    public function getSubtotal()
    {
        $subTotal = $this->_rootElement->find($this->subtotal, Locator::SELECTOR_XPATH)->getText();
        return $this->escapeCurrency($subTotal);
    }

    /**
     * Method that escapes currency symbols
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }
}
