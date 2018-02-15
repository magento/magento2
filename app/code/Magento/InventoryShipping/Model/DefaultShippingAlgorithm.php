<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterface;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\ShippingAlgorithmResultInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceItemSelectionInterfaceFactory;
use Magento\InventoryShipping\Model\ShippingAlgorithmResult\SourceSelectionInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var bool
     */
    private $isShippable;

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
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param SourceSelectionInterfaceFactory $sourceSelectionFactory
     * @param SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory
     * @param ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory
     * @param StoreManagerInterface $storeManager
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param StockResolverInterface $stockResolver
     * @param GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
     */
    public function __construct(
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        SourceSelectionInterfaceFactory $sourceSelectionFactory,
        SourceItemSelectionInterfaceFactory $sourceItemSelectionFactory,
        ShippingAlgorithmResultInterfaceFactory $shippingAlgorithmResultFactory,
        StoreManagerInterface $storeManager,
        WebsiteRepositoryInterface $websiteRepository,
        StockResolverInterface $stockResolver,
        GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
    ) {
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->shippingAlgorithmResultFactory = $shippingAlgorithmResultFactory;
        $this->sourceSelectionFactory = $sourceSelectionFactory;
        $this->sourceItemSelectionFactory = $sourceItemSelectionFactory;
        $this->storeManager = $storeManager;
        $this->websiteRepository = $websiteRepository;
        $this->stockResolver = $stockResolver;
        $this->getAssignedSourcesForStock = $getAssignedSourcesForStock;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderInterface $order): ShippingAlgorithmResultInterface
    {
        $this->isShippable = true;
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
            'isShippable' => $this->isShippable
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
            $sourceItems = $this->getSourceItemsBySku->execute($orderItem->getSku());

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

            if ($qtyToDeliver > 0.0001) {
                $this->isShippable = false;
            }
        }

        return $sourceItemSelections;
    }

    /**
     * @param int $storeId
     *
     * @return SourceItemInterface[]
     */
    private function getSourcesByStoreId($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $webstore = $this->websiteRepository->getById($store->getId());
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $webstore->getCode());

       return $this->getAssignedSourcesForStock->execute($stock->getStockId());
    }
}
