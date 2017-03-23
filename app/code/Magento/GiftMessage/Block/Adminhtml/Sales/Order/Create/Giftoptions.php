<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

/**
 * Adminhtml sales order create gift options block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
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
