<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Plugin\SalesInventory;

use Magento\InventorySales\Model\ReturnProcessor\ProcessItems;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;

class ProcessReturnQtyOnCreditMemoPlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var ProcessItems
     */
    private $processItems;

    /**
     * @var array
     */
    private $returnToStockItems = [];

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param ProcessItems $processItems
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        ProcessItems $processItems
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->processItems = $processItems;
    }

    /**
     * @param ReturnProcessor $subject
     * @param callable $proceed
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @param bool $isAutoReturn
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        ReturnProcessor $subject,
        callable $proceed,
        CreditmemoInterface $creditmemo,
        OrderInterface $order,
        array $returnToStockItems = [],
        $isAutoReturn = false
    ) {
        $itemsToRefund = $processedItems = [];
        $this->returnToStockItems = $returnToStockItems;
        foreach ($creditmemo->getItems() as $item) {
            $orderItem = $item->getOrderItem();
            $parentItemId = $orderItem->getParentItemId();
            $qty = (float)$item->getQty();
            if ($this->canReturnItem($orderItem->getId(), $qty, $parentItemId) && !$orderItem->isDummy()) {
                $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                    [$item->getProductId()]
                )[$item->getProductId()];
                $itemsToRefund[$itemSku] = ($itemsToRefund[$itemSku] ?? 0) + $qty;
                $processedQty = $orderItem->getQtyCanceled() - $orderItem->getQtyRefunded();
                $processedItems[$itemSku] = ($processedItems[$itemSku] ?? 0) + (float)$processedQty;
            }
        }

        $this->processItems->execute($order, $itemsToRefund, $processedItems, $returnToStockItems);
    }

    /**
     * @param int $orderItemId
     * @param int $qty
     * @param int $parentItemId
     * @return bool
     */
    private function canReturnItem($orderItemId, $qty, $parentItemId = null): bool
    {
        return (in_array($orderItemId, $this->returnToStockItems)
                || in_array($parentItemId, $this->returnToStockItems)) && $qty;
    }
}
