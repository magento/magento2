<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * {@inheritdoc}
 *
 * This shipping algorithm just iterates over all the sources one by one in no particular order
 */
class DefaultShippingAlgorithm implements ShippingAlgorithmInterface
{
    /**
     * @var ShippingAlgorithmResultResultFactory
     */
    private $shippingAlgorithmResultFactory;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceSelectionInterfaceFactory
     */
    private $sourceSelectionFactory;

    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param ShippingAlgorithmResultResultFactory $shippingAlgorithmResultFactory
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        ShippingAlgorithmResultResultFactory $shippingAlgorithmResultFactory
    ) {
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $sourceSelections = [];
        
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->isDeleted() || $orderItem->getParentItemId()) {
                continue;
            }

            $itemSku = $orderItem->getSku();
            $sourceItems = $this->sourceItemRepository->getBySku($itemSku)->getItems();

            $qtyToDeliver = $orderItem->getQtyOrdered();
            foreach ($sourceItems as $sourceItem) {
                if ($qtyToDeliver < 0.0001) {
                    break;
                }

                $sourceItemQty = $sourceItem->getQuantity();
                if ($sourceItemQty > 0) {
                    $qtyToDeduct = min($sourceItemQty, $qtyToDeliver);

                    $sourceSelection = $this->sourceSelectionFactory->create([
                        'sku' => $itemSku,
                        'sourceCode' => $sourceItem->getSourceCode(),
                        'qty' => $qtyToDeduct,
                        'qtyAvailable' => $sourceItemQty
                    ]);

                    $sourceSelections[$sourceItem->getSourceCode()][] = $sourceSelection;

                    $qtyToDeliver -= $qtyToDeduct;
                }
            }
        }

        $shippingResult = $this->shippingAlgorithmResultFactory->create([
            'sourceSelections' => $sourceSelections
        ]);

        return $shippingResult;
    }
}
