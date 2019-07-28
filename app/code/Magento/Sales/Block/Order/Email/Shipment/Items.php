<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Order\Email\Shipment;

/**
 * Sales Order Email Shipment items
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     * @return void
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getShipment());
    }
}
