<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Get a list of default source items for a given SKU list
 */
class GetDefaultSourceItemsBySkus
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
     * @SuppressWarnings(PHPMD.LongVariable)
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
     * @param array $skus
     * @return SourceItemInterface[]
     */
    public function execute(array $skus): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $skus, 'in')
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->create();

        return $this->sourceItemRepository->getList($searchCriteria)->getItems();
    }
}
