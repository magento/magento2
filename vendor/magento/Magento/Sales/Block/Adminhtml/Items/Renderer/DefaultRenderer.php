<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Block\Adminhtml\Items\Renderer;

use Magento\Sales\Model\Order\Item;

/**
 * Adminhtml sales order item renderer
 */
class DefaultRenderer extends \Magento\Sales\Block\Adminhtml\Items\AbstractItems
{
    /**
     * Get order item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_getData('item');//->getOrderItem();
    }
}
