<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

class InventoryTransfer implements ArgumentInterface
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
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @SuppressWarnings(PHPMD.LongVariables)
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->defaultSourceProvider = $defaultSourceProvider;
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
     * @return int
     */
    public function getProductsCount(): int
    {
        return count($this->bulkSessionProductsStorage->getProductsSkus());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultSourceName(): string
    {
        $defaultSource = $this->sourceRepository->get($this->defaultSourceProvider->getCode());
        return $defaultSource->getName();
    }
}
