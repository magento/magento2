<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Reorder;

use Magento\Sales\Model\Config;

class HasUnavailableProduct
{
    /**
     * @var Config
     */
    private $salesConfig;

    /**
     * @var OrderedProductAvailability
     */
    private $orderedProductAvailability;

    /**
     * @param Config $salesConfig
     * @param OrderedProductAvailability $orderedProductAvailability
     */
    public function __construct(
        Config $salesConfig,
        OrderedProductAvailability $orderedProductAvailability

    ) {
        $this->salesConfig = $salesConfig;
        $this->orderedProductAvailability = $orderedProductAvailability;
    }

    /**
     *  Check if order has products that unavailable for now.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function hasUnavailableProducts($order)
    {
        $unavailableProducts = [];
        foreach ($order->getItemsCollection($this->salesConfig->getAvailableProductTypes(), false) as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            if (!$this->orderedProductAvailability->checkAvailability($orderItem)) {
                $unavailableProducts[] = $orderItem->getSku();
            }
        }
        return $unavailableProducts;
    }
}
