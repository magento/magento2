<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Shipment\View;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractItems;

/**
 * Class Items
 * Shipment Items block on Shipment view page
 */
class Items extends AbstractItems
{
    /**
     * Get items data
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->_rootElement->getElements($this->rowItem);
        $data = [];

        foreach ($items as $item) {
            $itemData = [];

            $itemData += $this->parseProductName($item->find($this->product)->getText());
            $itemData['qty'] = $item->find($this->qty)->getText();

            $data[] = $itemData;
        }

        return $data;
    }
}
