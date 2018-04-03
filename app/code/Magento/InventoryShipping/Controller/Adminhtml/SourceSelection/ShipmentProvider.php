<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\SourceSelection;

use Magento\Shipping\Controller\Adminhtml\Order\ShipmentProvider as LegacyShipmentProvider;

class ShipmentProvider extends LegacyShipmentProvider
{
    /**
     * @inheritdoc
     */
    public function getShipment()
    {
        $sourceCode = $this->request->getParam('sourceCode');
        $items = $this->request->getParam('items');

        $shipmentItems = [];
        foreach ($items as $item) {
            if (empty($item['sources'])) {
                continue;
            }
            $orderItemId = $item['orderItemId'];
            foreach ($item['sources'] as $source) {
                if ($source['sourceCode'] == $sourceCode) {
                    $qty = ($shipmentItems[$orderItemId] ?? 0) + (float)$source['qtyToDeduct'];
                    $shipmentItems['items'][$orderItemId] = $qty;
                }
            }
        }

        return $shipmentItems;
    }
}
