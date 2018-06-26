<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryAdminUi\Model\BulkSessionProductsStorage;

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
     * @param SourceRepositoryInterface $sourceRepository
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @SuppressWarnings(PHPMD.LongVariables)
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        BulkSessionProductsStorage $bulkSessionProductsStorage
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
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
}
