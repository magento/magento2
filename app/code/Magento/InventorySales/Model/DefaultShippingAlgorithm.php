<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySales\Api\ShippingAlgorithmInterface;
use Magento\InventorySales\Api\ShippingAlgorithmResultInterface;
use Magento\InventorySales\Api\SourceSelectionInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * This shipping algorithm just iterates over all the sources one by one in no particular order.
 * @package Magento\InventorySales\Model
 */
class DefaultShippingAlgorithm implements ShippingAlgorithmInterface
{
    /**
     * @var DefaultShippingAlgorithmResultFactory
     */
    private $shippingAlgorithmResultFactory;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceSelectionInterfaceFactory
     */
    private $sourceSelectionFactory;

    /**
     * DefaultShippingAlgorithm constructor.
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param DefaultShippingAlgorithmResultFactory $shippingAlgorithmResultFactory
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        DefaultShippingAlgorithmResultFactory $shippingAlgorithmResultFactory
    ) {
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function get(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $sourceSelections = [];
        
        foreach ($order->getItems() as $orderItem) {
            $sourceItems = $this->getSourceItemsBySku($orderItem->getSku());

            $qtyToDeliver = $orderItem->getQtyOrdered();
            foreach ($sourceItems as $sourceItem) {
                if ($qtyToDeliver < 0.0001) {
                    break;
                }
                
                if ($sourceItem->getQuantity() > 0) {
                    $qtyToDeduct = min($sourceItem->getQuantity(), $qtyToDeliver);

                    $sourceSelection = $this->sourceSelectionFactory->create([
                        'sourceCode' => $sourceItem->getSourceCode(),
                        'qty' => $qtyToDeduct
                    ]);

                    $sourceSelections[$sourceItem->getSku()][] = $sourceSelection;

                    $qtyToDeliver -= $qtyToDeduct;
                }
            }
        }

        $shippingResult = $this->shippingAlgorithmResultFactory->create([
            'sourceSelections' => $sourceSelections
        ]);

        return $shippingResult;
    }

    /**
     * @param string $sku
     * @return SourceItemInterface[]
     */
    private function getSourceItemsBySku(string $sku): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();

        $sourceItemSearchResult = $this->sourceItemRepository->getList($searchCriteria);
        return $sourceItemSearchResult->getItems();
    }
}
