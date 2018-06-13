<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Get the default source item by product sku or return null if not existing
 */
class GetDefaultSourceItemBySku
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SourceItemRepositoryInterface $sourceItemRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceItemRepositoryInterface $sourceItemRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceItemRepository = $sourceItemRepository;
    }

    /**
     * @param string $productSku
     * @return SourceItemInterface|null
     */
    public function execute(string $productSku): ?SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        return count($sourceItems) ? reset($sourceItems) : null;
    }
}
