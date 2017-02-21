<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Shipping\Test\Block\Adminhtml\Order\Tracking\Item;

/**
 * Block for shipment tracking info table.
 */
class TrackingInfoTable extends Block
{
    /**
     * Selector for tracking number item.
     *
     * @var string
     */
    private $item = './/tfoot/tr[not(contains(@class,"no-display"))]';

    /**
     * Selector for a button to add new tracking number item.
     *
     * @var string
     */
    private $addButton = '[data-ui-id="shipment-tracking-save-button"]';

    /**
     * Creates tracking number.
     *
     * @param array $data
     */
    public function addTrackingNumber(array $data)
    {
        $trackingItemBlock = $this->getTrackingNumberItem();
        $trackingItemBlock->fillRow($data);

        $this->_rootElement->find($this->addButton)->click();
    }

    /**
     * Creates block for tracking number item.
     *
     * @return Item
     */
    private function getTrackingNumberItem()
    {
        return $this->blockFactory->create(
            Item::class,
            ['element' => $this->_rootElement->find($this->item, Locator::SELECTOR_XPATH)]
        );
    }
}
