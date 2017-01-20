<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Shipment\View;

use Magento\Sales\Test\Block\Adminhtml\Order\AbstractItems;

/**
 * Shipment Items block on Shipment view page.
 */
class Items extends AbstractItems
{
    /**
     * Get items data.
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->_rootElement->getElements($this->rowItem);
        $data = [];

        foreach ($items as $item) {
            $itemData = [];

            $itemData['product'] = preg_replace('/\n|\r/', '', $item->find($this->title)->getText());
            $itemData['sku'] = $this->getSku($item);
            $itemData['qty'] = $this->getQty($item);

            $data[] = $itemData;
        }

        return $data;
    }
}
