<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Traversable;

/**
 * Creates instance of InventoryRequestInterface by given InvoiceInterface object.
 * Only virtual type items will be used.
 */
class GetSourceSelectionResultFromInvoice
{
    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceSelectionServiceInterface $sourceSelectionService
     */
    public function __construct(
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        ItemRequestInterfaceFactory $itemRequestFactory,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceSelectionServiceInterface $sourceSelectionService
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
    }

    /**
     * @param InvoiceInterface $invoice
     * @return SourceSelectionResultInterface
     */
    public function execute(InvoiceInterface $invoice): SourceSelectionResultInterface
    {
        $order = $invoice->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

        /** @var InventoryRequestInterface $inventoryRequest */
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items' => $this->getSelectionRequestItems($invoice->getItems())
        ]);

        $selectionAlgorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        return $this->sourceSelectionService->execute($inventoryRequest, $selectionAlgorithmCode);
    }

    /**
     * @param InvoiceItemInterface[]|iterable $invoiceItems
     * @return array
     */
    private function getSelectionRequestItems(iterable $invoiceItems): array
    {
        $selectionRequestItems = [];
        foreach ($invoiceItems as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();

            if ($orderItem->isDummy() || !$orderItem->getIsVirtual()) {
                continue;
            }

            $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
            $qty = $this->castQty($invoiceItem->getOrderItem(), $invoiceItem->getQty());

            $selectionRequestItems[] = $this->itemRequestFactory->create([
                'sku' => $itemSku,
                'qty' => $qty,
            ]);
        }
        return $selectionRequestItems;
    }

    /**
     * @param OrderItemInterface $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(OrderItemInterface $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
