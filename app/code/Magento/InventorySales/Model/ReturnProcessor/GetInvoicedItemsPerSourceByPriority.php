<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice as InvoiceModel;
use Magento\Sales\Model\Order\Item as OrderItemModel;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Model\ReturnProcessor\GetSourceDeductedOrderItemsInterface;
use Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemFactory;
use Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResultFactory;
use Magento\InventorySalesApi\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResult;

class GetInvoicedItemsPerSourceByPriority implements GetSourceDeductedOrderItemsInterface
{
    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

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
     * @var SourceDeductedOrderItemFactory
     */
    private $sourceDeductedOrderItemFactory;

    /**
     * @var SourceDeductedOrderItemsResultFactory
     */
    private $sourceDeductedOrderItemsResultFactory;

    /**
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SourceDeductedOrderItemFactory $sourceDeductedOrderItemFactory
     * @param SourceDeductedOrderItemsResultFactory $sourceDeductedOrderItemsResultFactory
     */
    public function __construct(
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceDeductedOrderItemFactory $sourceDeductedOrderItemFactory,
        SourceDeductedOrderItemsResultFactory $sourceDeductedOrderItemsResultFactory
    ) {
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceDeductedOrderItemFactory = $sourceDeductedOrderItemFactory;
        $this->sourceDeductedOrderItemsResultFactory = $sourceDeductedOrderItemsResultFactory;
    }

    /**
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @return SourceDeductedOrderItemsResult[]
     */
    public function execute(OrderInterface $order, array $returnToStockItems): array
    {
        $invoicedItems = [];
        /** @var InvoiceModel $invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            foreach ($invoice->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($this->isValidItem($orderItem, $returnToStockItems)) {
                    $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
                    $invoicedItems[$itemSku] = ($invoicedItems[$itemSku] ?? 0) + $item->getQty();
                }
            }
        }
        $websiteId = (int)$order->getStore()->getWebsiteId();

        return $this->getSourceDeductedInvoiceItemsResult($invoicedItems, $websiteId);
    }

    /**
     * @param array $invoicedItems
     * @param int $websiteId
     * @return SourceDeductedOrderItemsResult[]
     */
    private function getSourceDeductedInvoiceItemsResult(array $invoicedItems, int $websiteId): array
    {
        $invoicedItemsToReturn = $result = [];
        $stockId = (int)$this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();
        foreach ($invoicedItems as $sku => $qty) {
            $sourceCode = $this->getSourceCodeWithHighestPriorityBySku($sku, $stockId);
            $invoicedItemsToReturn[$sourceCode][] = $this->sourceDeductedOrderItemFactory->create([
                'sku' => $sku,
                'quantity' => $qty
            ]);
        }

        foreach ($invoicedItemsToReturn as $sourceCode => $items) {
            $result[] = $this->sourceDeductedOrderItemsResultFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $items
            ]);
        }

        return $result;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return string
     */
    private function getSourceCodeWithHighestPriorityBySku(string $sku, int $stockId): string
    {
        $sourceCode = $this->defaultSourceProvider->getCode();
        try {
            $availableSourcesForProduct = $this->getSourceItemsBySku->execute($sku);
            $assignedSourcesToStock = $this->getSourcesAssignedToStockOrderedByPriority->execute($stockId);
            foreach ($assignedSourcesToStock as $assignedSource) {
                foreach ($availableSourcesForProduct as $availableSource) {
                    if ($assignedSource->getSourceCode() == $availableSource->getSourceCode()) {
                        $sourceCode = $assignedSource->getSourceCode();
                        break 2;
                    }
                }
            }
        } catch (LocalizedException $e) {
            //Use Default Source if the source can't be resolved
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
