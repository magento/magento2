<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\CatalogInventory\Helper\Stock as Helper;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin for Magento\CatalogInventory\Helper::addStockStatusToProducts.
 */
class AddStockStatusToProductsMultistockPlugin
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
     * @var IsProductInStockInterface
     */
    private $isProductInStockInterface;

    /**
     * @param StockResolverInterface $stockResolver
     * @param StoreManagerInterface $storeManager
     * @param IsProductInStockInterface $isProductInStockInterface
     */
    public function __construct(
        StockResolverInterface $stockResolver,
        StoreManagerInterface $storeManager,
        IsProductInStockInterface $isProductInStockInterface
    ) {
        $this->stockResolver = $stockResolver;
        $this->storeManager = $storeManager;
        $this->isProductInStockInterface = $isProductInStockInterface;
    }

    /**
     * Around plugin for Magento\CatalogInventory\Helper::addStockStatusToProducts.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Helper $subject
     * @param callable $proceed
     * @param AbstractCollection $productCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddStockStatusToProducts(
        Helper $subject,
        callable $proceed,
        AbstractCollection $productCollection
    ) {
        // We need to prevent the collection modification by plugins next in the chain.
        $temporaryCollection = clone $productCollection;
        $proceed($temporaryCollection);
        $this->addStockStatusToProducts($productCollection);
    }

    /**
     * Assign stock status information to products for MSI.
     *
     * @param  AbstractCollection $productCollection
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addStockStatusToProducts(AbstractCollection $productCollection)
    {
        /** @var WebsiteInterface $website */
        $website = $this->storeManager->getWebsite();
        /** @var StockInterface $stock */
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
        /** @var Product $product */
        foreach ($productCollection as $product) {
            $status = (int)$this->isProductInStockInterface->execute($product->getSku(), (int)$stock->getStockId());
            $product->setIsSalable($status);
        }
    }
}
