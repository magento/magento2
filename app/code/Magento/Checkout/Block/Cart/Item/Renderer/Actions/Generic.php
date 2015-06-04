<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Context;
use Magento\Framework\View\Element\Template;

class Generic extends Template
{
    /**
     * @var Context
     */
    protected $itemContext;

    /**
     * Returns current quote item
     *
     * @return Context
     */
    public function getItemContext()
    {
        return $this->itemContext;
    }

    /**
     * Set current quote item
     *
     * @param Context $itemContext
     */
    public function setItemContext(Context $itemContext)
    {
        $this->itemContext = $itemContext;
    }

    /**
     * Check if product is visible in site visibility
     *
     * @return bool
     */
    public function isProductVisibleInSiteVisibility()
    {
        return $this->getItemContext()->getQuoteItem()->getProduct()->isVisibleInSiteVisibility();
    }
}
