<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Inventory\Model\SourceItemFinderInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * {@inheritdoc}
 *
 * This shipping algorithm just iterates over all the sources one by one in no particular order
 */
class DefaultShippingAlgorithm implements ShippingAlgorithmInterface
{
    /**
     * @var SourceItemFinderInterface
     */
    private $sourceItemFinder;

    /**
     * @var ShippingAlgorithmResultFactory
     */
    private $shippingAlgorithmResultFactory;

    /**
     * @var SourceSelectionInterfaceFactory
     */
    private $sourceSelectionFactory;

    /**
     * @var SourceItemSelectionInterfaceFactory
     */
    private $sourceItemSelectionFactory;

    /**
     * @param SourceItemFinderInterface $sourceItemFinder
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory
     * @param ShippingAlgorithmResultFactory $shippingAlgorithmResultFactory
     */
    public function __construct(
        SourceItemFinderInterface $sourceItemFinder,
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory,
        ShippingAlgorithmResultFactory $shippingAlgorithmResultFactory
    ) {
        $this->sourceItemFinder = $sourceItemFinder;
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
        $this->sourceItemSelectionFactory = $sourceItemSelectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $sourceItemSelections = [];

        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->isDeleted() || $orderItem->getParentItemId()) {
                continue;
            }

            $itemSku = $orderItem->getSku();
            $sourceItems = $this->sourceItemFinder->findBySku($itemSku)->getItems();

            $qtyToDeliver = $orderItem->getQtyOrdered();
            foreach ($sourceItems as $sourceItem) {
                if ($qtyToDeliver < 0.0001) {
                    break;
                }

                $sourceItemQty = $sourceItem->getQuantity();
                if ($sourceItemQty > 0) {
                    $qtyToDeduct = min($sourceItemQty, $qtyToDeliver);

                    $sourceItemSelections[$sourceItem->getSourceCode()][] = $this->sourceItemSelectionFactory->create([
                        'sku' => $itemSku,
                        'qty' => $qtyToDeduct,
                        'qtyAvailable' => $sourceItemQty
                    ]);

                    $qtyToDeliver -= $qtyToDeduct;
                }
            }
        }

        $sourceSelections = [];
        foreach ($sourceItemSelections as $sourceCode => $itemSelections) {
            $sourceSelections[] = $this->sourceSelectionFactory->create([
                'sourceCode' => $sourceCode,
                'sourceItemSelections' => $itemSelections
            ]);
        }

        $shippingResult = $this->shippingAlgorithmResultFactory->create([
            'sourceSelections' => $sourceSelections
        ]);

        return $shippingResult;
    }
}
