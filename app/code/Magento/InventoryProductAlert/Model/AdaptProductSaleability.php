<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryProductAlert\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Model\Website;

/**
 * Adapt product saleability for multi source.
 */
class AdaptProductSaleability
{
    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
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
     * @param ProductInterface $product
     * @param Website $website
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isSalable(ProductInterface $product, Website $website): bool
    {
        /** @var StockInterface $stock */
        $stock = $this->stockResolver->get(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());
        $isSalable = $this->isProductSalable->execute($product->getSku(), (int)$stock->getStockId());

        return $isSalable;
    }
}
