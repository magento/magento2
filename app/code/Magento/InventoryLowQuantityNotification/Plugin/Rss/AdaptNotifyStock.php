<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\Rss;

use Magento\Catalog\Model\Rss\Product\NotifyStock;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\GetAdaptedNotifyStockCollection;

class AdaptNotifyStock
{
    /**
     * @var GetAdaptedNotifyStockCollection
     */
    private $getAdaptedNotifyStockCollection;

    /**
     * @param GetAdaptedNotifyStockCollection $getAdaptedNotifyStockCollection
     */
    public function __construct(
        GetAdaptedNotifyStockCollection $getAdaptedNotifyStockCollection
    ) {
        $this->getAdaptedNotifyStockCollection = $getAdaptedNotifyStockCollection;
    }

    /**
     * @param NotifyStock $subject
     * @param callable $proceed
     *
     * @return \Magento\Inventory\Model\ResourceModel\SourceItem\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItemsCollection(
        NotifyStock $subject,
        callable $proceed
    ) {
        $collection = $this->getAdaptedNotifyStockCollection->execute();

        return $collection;
    }
}
