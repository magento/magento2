<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Order history block on My Order page.
 */
class History extends Block
{
    /**
     * Locator for order id and order status.
     *
     * @var string
     */
    protected $customerOrders = '//tr[td[contains(.,"%d")] and td[contains(.,"%s")]]';

    /**
     * Item order.
     *
     * @var string
     */
    protected $itemOrder = '//tr[td[contains(@class, "id") and normalize-space(.)="%s"]]';

    /**
     * Order total css selector.
     *
     * @var string
     */
    protected $total = '.total span.price';

    /**
     * View button css selector.
     *
     * @var string
     */
    protected $viewButton = '.action.view';

    /**
     * Order history form selector.
     *
     * @var string
     */
    protected $formSelector = '#my-orders-table';

    /**
     * Check if order is visible in customer orders on frontend.
     *
     * @param array $order
     * @return bool
     */
    public function isOrderVisible($order)
    {
        $this->waitFormToLoad();
        return $this->_rootElement->find(
            sprintf($this->customerOrders, $order['id'], $order['status']),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Get order total.
     *
     * @param string $id
     * @return string
     */
    public function getOrderTotalById($id)
    {
        $this->waitFormToLoad();
        return $this->escapeCurrency($this->searchOrderById($id)->find($this->total)->getText());
    }

    /**
     * Get item order block.
     *
     * @param string $id
     * @return SimpleElement
     */
    protected function searchOrderById($id)
    {
        return $this->_rootElement->find(sprintf($this->itemOrder, $id), Locator::SELECTOR_XPATH);
    }

    /**
     * Open item order.
     *
     * @param string $id
     * @return void
     */
    public function openOrderById($id)
    {
        $this->waitFormToLoad();
        $this->searchOrderById($id)->find($this->viewButton)->click();
    }

    /**
     * Method that escapes currency symbols.
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }

    /**
     * Wait order history form to load via ajax.
     *
     * @return void
     */
    protected function waitFormToLoad()
    {
        $browser = $this->browser;
        $selector = $this->formSelector;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() ? true : null;
            }
        );
    }
}
