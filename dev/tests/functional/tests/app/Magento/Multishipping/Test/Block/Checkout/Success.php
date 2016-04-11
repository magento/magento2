<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Multishipping checkout success block
 */
class Success extends Block
{
    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '.button-set button';

    /**
     * 'Continue Shopping' link
     *
     * @var string
     */
    protected $continueShopping = '.action.continue';

    /**
     * Fill shipping address
     *
     * @return void
     */
    public function continueShopping()
    {
        $this->_rootElement->find($this->continue, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible('.please-wait');
    }

    /**
     * Get ids for placed order
     *
     * @param int $ordersNumber
     * @return array
     */
    public function getOrderIds($ordersNumber)
    {
        $continueShopping = $this->_rootElement->find($this->continueShopping);
        $this->_rootElement->waitUntil(
            function () use ($continueShopping) {
                return $continueShopping->isVisible() ? true : null;
            }
        );
        $orderIds = [];
        for ($i = 1; $i <= $ordersNumber; $i++) {
            $orderIds[] = $this->_rootElement->find(
                '//a[' . $i . '][contains(@href, "view/order_id")]',
                Locator::SELECTOR_XPATH
            )->getText();
        }

        return $orderIds;
    }
}
