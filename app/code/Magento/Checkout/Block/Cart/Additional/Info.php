<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart\Additional;

class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    protected $_item;

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     * @codeCoverageIgnore
     */
    public function setItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem
     * @codeCoverageIgnore
     */
    public function getItem()
    {
        return $this->_item;
    }
}
