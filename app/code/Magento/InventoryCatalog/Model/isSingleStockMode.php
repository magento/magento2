<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryApi\Api\StockRepositoryInterface;
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
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        StockRepositoryInterface $stockRepository
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(): bool
    {
        return !(count($this->sourceRepository->getList()->getItems()) > 1 ||
            count($this->stockRepository->getList()->getItems() > 1));
    }
}
