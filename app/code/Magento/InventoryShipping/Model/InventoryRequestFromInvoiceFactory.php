<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Framework\Exception\InputException;
use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Traversable;

/**
 * Creates instance of InventoryRequestInterface by given InvoiceInterface object.
 * Only virtual type items will be used.
 */
class InventoryRequestFromInvoiceFactory
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var StockByWebsiteIdResolver
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        ItemRequestInterfaceFactory $itemRequestFactory,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        InventoryRequestInterfaceFactory $inventoryRequestFactory
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
    }

    /**
     * @param InvoiceInterface $invoice
     * @return InventoryRequestInterface
     * @throws InputException
     */
    public function create(InvoiceInterface $invoice): InventoryRequestInterface
    {
        $order = $invoice->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        /** @var InventoryRequestInterface $inventoryRequest */
        return $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items' => $this->getSelectionRequestItems($invoice->getItems())
        ]);
    }

    /**
     * @param InvoiceItemInterface[]|Traversable $invoiceItems
     * @return array
     * @throws InputException
     */
    private function getSelectionRequestItems(Traversable $invoiceItems): array
    {
        $selectionRequestItems = [];
        foreach ($invoiceItems as $invoiceItem) {
            if (!$invoiceItem->getOrderItem()->getIsVirtual()) {
                continue;
            }

            $itemSku = $invoiceItem->getSku() ?: $this->getSkusByProductIds->execute(
                [$invoiceItem->getProductId()]
            )[$invoiceItem->getProductId()];
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
