<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Item\Price;

use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Item price render block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Renderer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var AbstractItem
     */
    protected $item;

    /**
     * Set item for render
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function setItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get quote item
     *
     * @return AbstractItem
     */
    public function getItem()
    {
        return $this->item;
    }
}
