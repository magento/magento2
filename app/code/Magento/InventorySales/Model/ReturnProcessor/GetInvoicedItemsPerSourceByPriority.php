<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magento\Sales\Model\Order\Item as OrderItemModel;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Framework\Exception\LocalizedException;

class GetInvoicedItemsPerSourceByPriority
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @param OrderModel $order
     * @param array $returnToStockItems
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function execute(OrderModel $order, array $returnToStockItems): array
    {
        $invoicedItems = $invoicedItemsToReturn = [];
        /** @var InvoiceModel $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            foreach ($invoice->getItems() as $item) {
                if ($this->isValidItem($item->getOrderItem(), $returnToStockItems)) {
                    $itemSku = $item->getSku() ?: $this->getSkusByProductIds->execute(
                        [$item->getProductId()]
                    )[$item->getProductId()];
                    $invoicedItems[$itemSku] = ($invoicedItems[$itemSku] ?? 0) + $item->getQty();
                }
            }
        }

        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();
        foreach ($invoicedItems as $sku => $qty) {
            $sourceCode = $this->getSourceCodeWithHighestPriorityBySku($sku, $stockId);
            $invoicedItemsToReturn[$sourceCode][$sku] = $qty;
        }

        return $invoicedItemsToReturn;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return string
     */
    private function getSourceCodeWithHighestPriorityBySku(string $sku, int $stockId): string
    {
        try {
            $availableSourcesForProduct = $this->getSourceItemsBySku->execute($sku);
            $assignedSourcesToStock = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
            foreach ($availableSourcesForProduct as $availableSource) {
                foreach ($assignedSourcesToStock as $assignedSource) {
                    if ($assignedSource->getSourceCode() == $availableSource->getSourceCode()) {
                        $sourceCode = $assignedSource->getSourceCode();
                        break 2;
                    }
                }
            }
        } catch (LocalizedException $e) {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        return $sourceCode;
    }

    /**
     * @param OrderItemModel $orderItem
     * @param array $returnToStockItems
     * @return bool
     */
    private function isValidItem(OrderItemModel $orderItem, array $returnToStockItems): bool
    {
        return (in_array($orderItem->getId(), $returnToStockItems)
                || in_array($orderItem->getParentItemId(), $returnToStockItems))
                && $orderItem->getIsVirtual()
                && !$orderItem->isDummy();
    }
}
