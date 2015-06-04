<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer;

class Context
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    protected $quoteItem;

    /**
     * Returns current quote item
     *
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    public function getQuoteItem()
    {
        return $this->quoteItem;
    }

    /**
     * Set current quote item
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     */
    public function setQuoteItem($quoteItem)
    {
        $this->quoteItem = $quoteItem;
    }
}
