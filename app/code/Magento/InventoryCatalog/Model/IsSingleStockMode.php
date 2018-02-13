<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * @inheritdoc
 */
class IsSingleStockMode implements IsSingleStockModeInterface
{
    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(SourceRepositoryInterface $sourceRepository)
    {
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        $enabledSourcesCount = 0;
        $availableSources = $this->sourceRepository->getList()->getItems();

        /** @var SourceInterface $availableSource */
        foreach ($availableSources as $availableSource) {
            if ($availableSource->isEnabled()) {
                $enabledSourcesCount++;
            }
        }

        return ($enabledSourcesCount < 2);
    }
}
