<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\AsyncSetDataToLegacyCatalogInventory;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Inventory\Model\SourceItemRepository;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\SetDataToLegacyCatalogInventory;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

class Consumer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SourceItemRepository
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SetDataToLegacyCatalogInventory
     */
    private $setDataToLegacyCatalogInventory;

    /**
     * Consumer constructor.
     * @param SerializerInterface $serializer
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        SerializerInterface $serializer,
        SourceItemRepositoryInterface $sourceItemRepository,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory
    ) {
        $this->serializer = $serializer;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyCatalogInventory = $setDataToLegacyCatalogInventory;
    }

    /**
     * Processing batch operations for legacy stock synchronization
     *
     * @param OperationInterface $operation
     * @return void
     */
    public function processOperations(OperationInterface $operation): void
    {
        $skus = $this->serializer->unserialize($operation->getSerializedData());

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->addFilter(SourceItemInterface::SKU, $skus, 'in')
            ->create();

        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $this->setDataToLegacyCatalogInventory->execute($sourceItems);
    }
}
