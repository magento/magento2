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
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Is source inventory of certain order is manageable
 */
class IsOrderSourceManageable
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
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param GetStockItemConfiguration $getStockItemConfiguration
     * @param StockRepositoryInterface $stockRepository
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        GetStockItemConfiguration $getStockItemConfiguration,
        StockRepositoryInterface $stockRepository,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->productRepository = $productRepository;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->stockRepository = $stockRepository;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
    }

    /**
     * Check if source manageable for certain order
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function execute(OrderInterface $order): bool
    {
        $stocks = $this->stockRepository->getList()->getItems();
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            if (!$this->isSourceItemManagementAllowedForProductType->execute($orderItem->getProductType())) {
                continue;
            }

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
