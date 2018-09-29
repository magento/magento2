<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\ViewModel;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Model\GetSourceCodesBySkusInterface;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;

class SourcesSelection implements ArgumentInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var BulkSessionProductsStorage
     */
    private $bulkSessionProductsStorage;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetSourceCodesBySkusInterface
     */
    private $getSourceCodesBySkus;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetSourceCodesBySkusInterface $getSourceCodesBySkus
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @SuppressWarnings(PHPMD.LongVariables)
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetSourceCodesBySkusInterface $getSourceCodesBySkus,
        BulkSessionProductsStorage $bulkSessionProductsStorage
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getSourceCodesBySkus = $getSourceCodesBySkus;
    }

    /**
     * Get a list of available sources
     * @return SourceInterface[]
     */
    public function getSources(): array
    {
        return $this->sourceRepository->getList()->getItems();
    }

    /**
     * Get a list of sources assigned to the products selection
     * @return SourceInterface[]
     */
    public function getAssignedSources(): array
    {
        $skus = $this->bulkSessionProductsStorage->getProductsSkus();
        $sourceCodes = $this->getSourceCodesBySkus->execute($skus);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                SourceItemInterface::SOURCE_CODE,
                $sourceCodes,
                'in'
            )
            ->create();

        return $this->sourceRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @return int
     */
    public function getProductsCount(): int
    {
        return count($this->bulkSessionProductsStorage->getProductsSkus());
    }
}
