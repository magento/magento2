<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Reorder;

use Magento\Sales\Model\Config;

/**
 * Class UnavailableProductsProvider
 */
class UnavailableProductsProvider
{
    /**
     * @var Config
     */
    private $salesConfig;

    /**
     * @var OrderedProductAvailabilityChecker
     */
    private $orderedProductAvailabilityChecker;

    /**
     * @param Config $salesConfig
     * @param OrderedProductAvailabilityChecker $orderedProductAvailabilityChecker
     */
    public function __construct(
        Config $salesConfig,
        OrderedProductAvailabilityChecker $orderedProductAvailabilityChecker
    ) {
        $this->salesConfig = $salesConfig;
        $this->orderedProductAvailabilityChecker = $orderedProductAvailabilityChecker;
    }

    /**
     * Gets products that are unavailable for the order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getForOrder($order)
    {
        $unavailableProducts = [];
        foreach ($order->getItemsCollection($this->salesConfig->getAvailableProductTypes(), false) as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            if (!$this->orderedProductAvailabilityChecker->isAvailable($orderItem)) {
                $unavailableProducts[] = $orderItem->getSku();
            }
        }
        return $unavailableProducts;
    }
}
