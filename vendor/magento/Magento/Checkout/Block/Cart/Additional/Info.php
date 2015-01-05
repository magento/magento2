<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Block\Cart\Additional;

class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Quote\Item\AbstractItem
     */
    protected $_item;

    /**
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function setItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Quote\Item\AbstractItem
     */
    public function getItem()
    {
        return $this->_item;
    }
}
