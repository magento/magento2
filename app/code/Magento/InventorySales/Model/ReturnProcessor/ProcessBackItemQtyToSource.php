<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySales\Model\ReturnProcessor\Request\BackItemQtyRequest;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

class ProcessBackItemQtyToSource
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @param StockResolverInterface $stockResolver
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        StockResolverInterface $stockResolver,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->stockResolver = $stockResolver;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param BackItemQtyRequest $backItemQtyRequest
     * @param SalesChannelInterface $salesChannel
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(
        BackItemQtyRequest $backItemQtyRequest,
        SalesChannelInterface $salesChannel
    ) {
        $backQty = $backItemQtyRequest->getQuantity();
        if ($backQty <= 0) {
            return;
        }

        $stockId = (int)$this->stockResolver->get($salesChannel->getType(), $salesChannel->getCode())->getStockId();

        $stockItemConfiguration = $this->getStockItemConfiguration->execute(
            $backItemQtyRequest->getSku(),
            $stockId
        );

        if (!$stockItemConfiguration->isManageStock()) {
            return;
        }

        $sourceItem = $this->getSourceItemBySourceCodeAndSku(
            $backItemQtyRequest->getSourceCode(),
            $backItemQtyRequest->getSku()
        );

        if (!empty($sourceItem)) {
            $sourceItem->setQuantity($sourceItem->getQuantity() + $backQty);
            $this->sourceItemsSave->execute([$sourceItem]);
        }
    }

    /**
     * Returns source item from specific source by given SKU. Return null if source item is not found
     *
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemInterface|null
     */
    private function getSourceItemBySourceCodeAndSku(string $sourceCode, string $sku)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItemsResult = $this->sourceItemRepository->getList($searchCriteria);

        return $sourceItemsResult->getTotalCount() > 0 ? current($sourceItemsResult->getItems()) : null;
    }
}
