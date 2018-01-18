<?php

namespace Magento\CatalogInventory\Services;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;

/**
 * Class GetProductIsQtyDecimalService
 */
class GetProductStockIsQtyDecimalService
{
    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @param StockItemRepositoryInterface $stockItemRepository
     *
     * @return void
     */
    public function __construct(StockItemRepositoryInterface $stockItemRepository)
    {
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function execute($productId)
    {
        $productStock = $this->stockItemRepository->get($productId);

        return (bool) $productStock->getIsQtyDecimal();
    }
}
