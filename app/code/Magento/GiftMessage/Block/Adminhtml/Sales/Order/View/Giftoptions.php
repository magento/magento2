<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\View;

use Magento\Backend\Block\Template;

/**
 * Adminhtml sales order view gift options block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Giftoptions extends Template
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
