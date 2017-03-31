<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Page;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Main dashboard block.
 */
class Main extends Block
{
    /**
     * Selector for Revenue prices.
     *
     * @var string
     */
    protected $revenuePriceBlock = '.dashboard-totals-list li:first-child .price';

    /**
     * Item xpath selector.
     *
     * @var string
     */
    private $itemSelector = '//span[contains(text(), "%s")]/following-sibling::strong';

    /**
     * Get Revenue price block.
     *
     * @return string
     */
    public function getRevenuePrice()
    {
        return $this->_rootElement->find($this->revenuePriceBlock)->getText();
    }

    /**
     * Get dashboard orders information.
     *
     * @param array $items
     * @return array
     */
    public function getDashboardOrder(array $items)
    {
        $order = [];
        foreach ($items as $item) {
            $selector = sprintf($this->itemSelector, $item);
            $order[strtolower($item)] = $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->getText();
        }
        return $order;
    }
}
