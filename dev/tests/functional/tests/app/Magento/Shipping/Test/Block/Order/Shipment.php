<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Order;

use Magento\Shipping\Test\Block\Order\Shipment\Items;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Shipment
 * Shipment view block on shipment view page
 */
class Shipment extends Block
{
    /**
     * Shipment item block
     *
     * @var string
     */
    protected $shipmentItemBlock = '//*[@class="order-title" and contains(.,"%d")]';

    /**
     * Shipment content block
     *
     * @var string
     */
    protected $shipmentContent = '/following-sibling::div[contains(@class,"order-items-shipment")][1]';

    /**
     * Get item shipment block
     *
     * @param int $id
     * @return Items
     */
    public function getItemShipmentBlock($id)
    {
        $selector = sprintf($this->shipmentItemBlock, $id) . $this->shipmentContent;
        return $this->blockFactory->create(
            \Magento\Shipping\Test\Block\Order\Shipment\Items::class,
            ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
        );
    }
}
