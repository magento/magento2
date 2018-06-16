<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssignAdminUi\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryMassSourceAssignAdminUi\Model\MassAssignSessionStorage;

class SourcesSelection implements ArgumentInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var MassAssignSessionStorage
     */
    private $massAssignSessionStorage;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param MassAssignSessionStorage $massAssignSessionStorage
     * @SuppressWarnings(PHPMD.LongVariables)
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        MassAssignSessionStorage $massAssignSessionStorage
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->massAssignSessionStorage = $massAssignSessionStorage;
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
        return count($this->massAssignSessionStorage->getProductsSkus());
    }
}
