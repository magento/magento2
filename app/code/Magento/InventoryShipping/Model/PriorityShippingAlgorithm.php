<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryShipping\Model\PriorityShippingAlgorithm\GetSourceItemBySku;
use Magento\InventoryShipping\Model\PriorityShippingAlgorithm\GetSourcesByStoreId;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceItemSelectionInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceItemSelectionInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * {@inheritdoc}
 * This shipping algorithm just iterates over all the sources one by one in priority order
 */
class PriorityShippingAlgorithm implements ShippingAlgorithmInterface
{
    /**
     * @var SourceSelectionInterfaceFactory
     */
    private $sourceSelectionFactory;

    /**
     * @var SourceItemSelectionInterfaceFactory
     */
    private $sourceItemSelectionFactory;

    /**
     * @var ShippingAlgorithmResultInterfaceFactory
     */
    private $shippingAlgorithmResultFactory;

    /**
     * @var GetSourceItemBySku
     */
    private $getSourceItemBySku;

    /**
     * @var GetSourcesByStoreId
     */
    private $getSourcesByStoreId;

    /**
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory
     * @param ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory
     * @param GetSourceItemBySku $getSourceItemBySku
     * @param GetSourcesByStoreId $getSourcesByStoreId
     */
    public function __construct(
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory,
        ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory,
        GetSourceItemBySku $getSourceItemBySku,
        GetSourcesByStoreId $getSourcesByStoreId
    ) {
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
        $this->sourceItemSelectionFactory = $sourceItemSelectionFactory;
        $this->getSourceItemBySku = $getSourceItemBySku;
        $this->getSourcesByStoreId = $getSourcesByStoreId;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $isShippable = true;
        $storeId = $order->getStoreId();
        $sources = $this->getSourcesByStoreId->execute((int)$storeId);
        $sourceItemSelections = [];

        /** @var OrderItemInterface|OrderItem $orderItem */
        foreach ($order->getItems() as $orderItem) {
            $itemSku = $orderItem->getSku();
            $qtyToDeliver = $orderItem->getQtyOrdered();

            //check if order item is not delivered yet
            if ($orderItem->isDeleted() || $orderItem->getParentItemId() || $this->isZero((float)$qtyToDeliver)) {
                continue;
            }

            foreach ($sources as $source) {
                if (!$source->isEnabled()) {
                    continue;
                }

                $sourceItem = $this->getSourceItemBySku->execute($source->getSourceCode(), $itemSku);
                $sourceItemQty = $sourceItem->getQuantity();
                $qtyToDeduct = min($sourceItemQty, $qtyToDeliver);

                // check if source has some qty of SKU, so it's possible to take them into account
                if ($this->isZero((float)$sourceItemQty)) {
                    continue;
                }

                $sourceItemSelection = $this->sourceItemSelectionFactory->create(
                    [
                        'sku' => $itemSku,
                        'qty' => $qtyToDeduct,
                        'qtyAvailable' => $sourceItemQty,
                    ]
                );

                $sourceItemSelections = $this->updateSourceItemSelections(
                    $sourceItemSelections,
                    $sourceItemSelection,
                    $sourceItem
                );

                $qtyToDeliver -= $qtyToDeduct;
            }

            // if we go throw all sources from the stock and there is still some qty to delivery,
            // then it doesn't have enough items to delivery
            if (!$this->isZero($qtyToDeliver)) {
                $isShippable = false;
            }
        }

        $sourceSelections = $this->createSourceSelection($sourceItemSelections);

        return $this->shippingAlgorithmResultFactory->create([
            'sourceSelections' => $sourceSelections,
            'isShippable' => $isShippable
        ]);
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }

    /**
     * @param SourceItemSelectionInterface[] $sourceItemSelections
     * @return SourceSelectionInterface[]
     */
    private function createSourceSelection($sourceItemSelections): array
    {
        $sourceSelections = [];
        foreach ($sourceItemSelections as $sourceCode => $items) {
            $sourceSelections[] = $this->sourceSelectionFactory->create(
                [
                    'sourceCode' => $sourceCode,
                    'sourceItemSelections' => $items
                ]
            );
        }
        return $sourceSelections;
    }

    /**
     * @param SourceItemSelectionInterface[] $sourceItemSelections
     * @param SourceItemSelectionInterface $sourceItemSelection
     * @param SourceItemInterface $sourceItem
     * @return SourceItemSelectionInterface[]
     */
    private function updateSourceItemSelections(
        array $sourceItemSelections,
        SourceItemSelectionInterface $sourceItemSelection,
        SourceItemInterface $sourceItem
    ): array {
        if (isset($sourceItemSelections[$sourceItem->getSourceCode()])) {
            $sourceItemSelections[$sourceItem->getSourceCode()][] = $sourceItemSelection;
        } else {
            $sourceItemSelections[$sourceItem->getSourceCode()] = [$sourceItemSelection];
        }
        return $sourceItemSelections;
    }
}
