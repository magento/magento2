<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceItemSelectionInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceItemSelectionInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * {@inheritdoc}
 * This shipping algorithm just iterates over all the sources one by one in priority order
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var GetAssignedSourcesForStockInterface
     */
    private $getAssignedSourcesForStock;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory
     * @param ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockResolverInterface $stockResolver
     * @param GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory,
        ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver,
        GetAssignedSourcesForStockInterface $getAssignedSourcesForStock,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemInterfaceFactory $sourceItemFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
        $this->sourceItemSelectionFactory = $sourceItemSelectionFactory;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
        $this->getAssignedSourcesForStock = $getAssignedSourcesForStock;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemFactory = $sourceItemFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $isShippable = true;
        $storeId = $order->getStoreId();
        $sources = $this->getSourcesByStoreId((int)$storeId);
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

                $sourceItem = $this->getStockItemBySku($source->getSourceCode(), $itemSku);
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
     * Retrieve sources are related to current stock that are ordered by priority
     *
     * @param int $storeId
     *
     * @return SourceInterface[]
     */
    private function getSourcesByStoreId(int $storeId): array
    {
        $store = $this->storeManager->getStore($storeId);
        $website = $this->websiteRepository->getById($store->getWebsiteId());
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());

        return $this->getAssignedSourcesForStock->execute((int)$stock->getStockId());
    }

    /**
     * Retrieve stock item from specific source by SKU
     *
     * @param string $stockCode
     * @param string $sku
     *
     * @return SourceItemInterface
     */
    private function getStockItemBySku(string $stockCode, string $sku): SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_CODE, $stockCode)
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItemsResult = $this->sourceItemRepository->getList($searchCriteria);

        if ($sourceItemsResult->getTotalCount() > 0) {
            $sourceItems = $sourceItemsResult->getItems();
            return reset($sourceItems);
        }

        return $this->sourceItemFactory->create();
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
