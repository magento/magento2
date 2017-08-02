<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer\Actions;

use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic
 *
 * @since 2.0.0
 */
class Generic extends Template
{
    /**
     * @var AbstractItem
     * @since 2.0.0
     */
    protected $item;

    /**
     * Returns current quote item
     *
     * @return AbstractItem
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set current quote item
     *
     * @param AbstractItem $item
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setItem(AbstractItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Check if product is visible in site visibility
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isProductVisibleInSiteVisibility()
    {
        return $this->getItem()->getProduct()->isVisibleInSiteVisibility();
    }

    /**
     * Check if cart item is virtual
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isVirtual()
    {
        return (bool)$this->getItem()->getIsVirtual();
    }
}
