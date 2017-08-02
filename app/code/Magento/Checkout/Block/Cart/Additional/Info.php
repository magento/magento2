<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Block\Cart\Additional;

/**
 * @api
 * @since 2.0.0
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem
     * @since 2.0.0
     */
    protected $_item;

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item\AbstractItem
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getItem()
    {
        return $this->_item;
    }
}
