<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Block\Item\Price;

use Magento\Sales\Model\Quote\Item\AbstractItem;

/**
 * Item price render block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function setItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
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
