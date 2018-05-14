<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Retrieve product stock status for given product sku.
 */
class GetProductStockStatusForCurrentWebsiteBySku
{
    /**
     * @var StockStatusInterfaceFactory
     */
    private $stockStatusFactory;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param StockStatusInterfaceFactory $stockStatusFactory
     * @param IsProductSalableInterface $isProductSalable
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        StockStatusInterfaceFactory $stockStatusFactory,
        IsProductSalableInterface $isProductSalable,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        GetProductSalableQtyInterface $getProductSalableQty,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->stockStatusFactory = $stockStatusFactory;
        $this->isProductSalable = $isProductSalable;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param string $sku
     * @return StockStatusInterface
     * @throws \Magento\Framework\Exception\InputException in case requested product doesn't exist.
     */
    public function execute(string $sku): StockStatusInterface
    {
        $productIds = $this->getProductIdsBySkus->execute([$sku]);
        $productId = reset($productIds);
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $qty = $this->getProductSalableQty->execute($sku, $stockId);
        $status = (int)$this->isProductSalable->execute($sku, $stockId);

        $stockStatus = $this->stockStatusFactory->create();
        $stockStatus->setProductId($productId);
        $stockStatus->setStockId($stockId);
        $stockStatus->setStockStatus($status);
        $stockStatus->setQty($qty);

        return $stockStatus;
    }
}
