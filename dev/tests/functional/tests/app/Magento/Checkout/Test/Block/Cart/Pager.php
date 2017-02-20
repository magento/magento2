<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Cart;

use Magento\Mtf\Block\Block;

/**
 * Class Pager
 * Pager block on the shopping cart page
 */
class Pager extends Block
{
    /**
     * Pages list
     *
     * @var string
     */
    protected $pages = '.pages';

    /**
     * Items qty block
     *
     * @var string
     */
    protected $amountToolbar = '.toolbar-number';

    /**
     * Get Pages element from the pager block
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getPagesBlock()
    {
        return $this->_rootElement->find($this->pages);
    }

    /**
     * Get Amount toolbar block from pager block
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getAmountToolbar()
    {
        return $this->_rootElement->find($this->amountToolbar);
    }
}
