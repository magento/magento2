<?php

namespace Magento\CatalogInventory\Helper;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;

/**
 * Class GetProductIsQtyDecimalService
 */
class ProductStockIsQtyDecimal
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
