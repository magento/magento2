<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Page;

use Magento\Mtf\Block\Block;

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
     * Get Revenue price block.
     *
     * @return string
     */
    public function getRevenuePrice()
    {
        return $this->_rootElement->find($this->revenuePriceBlock)->getText();
    }
}
