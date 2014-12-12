<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

/**
 * Adminhtml sales order create gift options block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
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
