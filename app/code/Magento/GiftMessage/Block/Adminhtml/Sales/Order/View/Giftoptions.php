<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\View;

/**
 * Adminhtml sales order view gift options block
 *
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
class Giftoptions extends \Magento\Backend\Block\Template
{
    /**
     * Get order item object from parent block
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getItem()
    {
        return $this->getParentBlock()->getData('item');
    }
}
