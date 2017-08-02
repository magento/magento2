<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\View;

/**
 * Adminhtml sales order view gift options block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Giftoptions extends \Magento\Backend\Block\Template
{
    /**
     * Get order item object from parent block
     *
     * @return \Magento\Sales\Model\Order\Item
     * @since 2.0.0
     */
    public function getItem()
    {
        return $this->getParentBlock()->getData('item');
    }
}
