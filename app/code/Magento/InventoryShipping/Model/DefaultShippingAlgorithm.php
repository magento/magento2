<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceItemSelectionInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * {@inheritdoc}
 *
 * This shipping algorithm just iterates over all the sources one by one in no particular order
 */
class DefaultShippingAlgorithm implements ShippingAlgorithmInterface
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

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
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory
     * @param ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory
     */
    public function __construct(
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory,
        ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory
    ) {
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
        $this->sourceItemSelectionFactory = $sourceItemSelectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $sourceItemSelectionsData = $this->getSourceItemSelectionsData($order);

        $sourceSelections = [];
        foreach ($sourceItemSelectionsData as $sourceCode => $sourceItemSelections) {
            $sourceSelections[] = $this->sourceSelectionFactory->create([
                'sourceCode' => $sourceCode,
                'sourceItemSelections' => $sourceItemSelections,
            ]);
        }

        $shippingResult = $this->shippingAlgorithmResultFactory->create([
            'sourceSelections' => $sourceSelections,
        ]);
        return $shippingResult;
    }

    /**
     * Key is source code, value is list of SourceItemSelectionInterface related to this source
     * @param OrderInterface $order
     * @return array
     */
    private function getSourceItemSelectionsData(OrderInterface $order): array
    {
        $sourceItemSelections = [];

        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->isDeleted() || $orderItem->getParentItemId()) {
                continue;
            }

            $itemSku = $orderItem->getSku();
            $sourceItems = $this->getSourceItemsBySku->execute($itemSku)->getItems();

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
                        'qtyAvailable' => $sourceItemQty,
                    ]);

                    $qtyToDeliver -= $qtyToDeduct;
                }
            }
        }
        return $sourceItemSelections;
    }
}
