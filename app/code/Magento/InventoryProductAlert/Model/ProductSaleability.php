<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryProductAlert\Model;

use \Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use \Magento\InventorySalesApi\Api\StockResolverInterface;
use \Magento\InventoryApi\Api\Data\StockInterface;
use \Magento\InventorySalesApi\Api\IsProductSalableInterface;

/**
 * Checks product saleability
 *
 * Class ProductSaleability
 */
class ProductSaleability
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var \Magento\InventorySalesApi\Api\IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * ProductSaleability constructor.
     * @param StockResolverInterface $stockResolver
     * @param IsProductSalableInterface $isProductSalable
     */
    public function __construct(
        StockResolverInterface $stockResolver,
        IsProductSalableInterface $isProductSalable
    ) {
        $this->stockResolver = $stockResolver;
        $this->isProductSalable = $isProductSalable;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Store\Model\Website $website
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isSalable(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Store\Model\Website $website
    ) : bool {
        /** @var StockInterface $stock */
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
        $result = $this->isProductSalable->execute($product->getSku(), $stock->getStockId());
        return $result;
    }
}
