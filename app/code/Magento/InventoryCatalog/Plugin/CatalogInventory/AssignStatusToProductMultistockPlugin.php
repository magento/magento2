<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Helper\Stock as Helper;
use Magento\Inventory\Model\GetProductQuantityInStock;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for Magento\CatalogInventory\Helper::AssignStatusToProduct.
 */
class AssignStatusToProductMultistockPlugin
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetProductQuantityInStock
     */
    private $getProductQuantityInStock;

    /**
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     * @param GetProductQuantityInStock $getProductQuantityInStock
     */
    public function __construct(
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        GetProductQuantityInStock $getProductQuantityInStock
    ) {
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
        $this->getProductQuantityInStock = $getProductQuantityInStock;
    }

    /**
     * Around plugin for Magento\CatalogInventory\Helper::AssignStatusToProduct.
     *
     * @param Helper $subject
     * @param callable $proceed
     * @param Product $product
     * @param int|null $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAssignStatusToProduct(
        Helper $subject,
        callable $proceed,
        Product $product,
        $status = null
    ) {
        // We need it to not prevent the execution of all the plugins next in the chain.
        $proceed($product, $status);
        $this->assignStatusToProduct($product, $status);
    }

    /**
     * Assign stock status information to product for MSI.
     *
     * @param Product $product
     * @param int|null $status
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function assignStatusToProduct(Product $product, $status)
    {
        if ($status === null) {
            /** @var WebsiteInterface $website */
            $website = $this->storeManager->getWebsite();
            /** @var StockInterface $stock */
            $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
            /**
             * Temporary solution.
             * Now we cannot use Magento\Inventory\Model\IsProductInStock::execute because otherwise
             * plugin Magento\InventorySales\Plugin\InventoryApi\BackorderStockStatusPlugin::aroundExecute
             * will cause recursive calls to self::aroundAssignStatusToProduct.
             */
            $status = (int)$this->getProductQuantityInStock->execute($product->getSku(), (int)$stock->getStockId()) > 0;
        }
        $product->setIsSalable($status);
    }
}
