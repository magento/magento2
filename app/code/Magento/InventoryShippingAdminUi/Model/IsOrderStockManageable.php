<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryConfiguration\Model\GetStockItemConfiguration;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Is stock manageable for certain order
 */
class IsOrderStockManageable
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var GetStockItemConfiguration
     */
    private $getStockItemConfiguration;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param GetStockItemConfiguration $getStockItemConfiguration
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        GetStockItemConfiguration $getStockItemConfiguration,
        StockRepositoryInterface $stockRepository
    ) {
        $this->productRepository = $productRepository;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Check if stock manageable for certain order
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function execute(OrderInterface $order): bool
    {
        $stocks = $this->stockRepository->getList();
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            $productId = $orderItem->getProductId();
            /** @var Product $product */
            $product = $this->productRepository->getById($productId);
            /** @var StockInterface $stock */
            foreach ($stocks as $stock) {
                $inventoryConfiguration = $this->getStockItemConfiguration->execute(
                    $product->getSku(),
                    $stock->getStockId()
                );

                if ($inventoryConfiguration->isManageStock()) {
                    return true;
                }
            }
        }

        return false;
    }
}
