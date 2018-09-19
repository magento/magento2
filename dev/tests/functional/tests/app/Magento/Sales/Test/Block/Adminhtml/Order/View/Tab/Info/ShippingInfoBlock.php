<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info;

use Magento\Mtf\Block\Block;

/**
 * Representation of Order Shipping Information.
 */
class ShippingInfoBlock extends Block
{
    /**
     * Selector for a shipment tracking popup.
     *
     * @var string
     */
    private $trackingPopupLink = '#linkId';

    /**
     * Opens the shipment tracking popup.
     *
     * @return void
     */
    public function openTrackingPopup()
    {
        $popup = $this->_rootElement->find($this->trackingPopupLink);
        $popup->click();
    }
}
