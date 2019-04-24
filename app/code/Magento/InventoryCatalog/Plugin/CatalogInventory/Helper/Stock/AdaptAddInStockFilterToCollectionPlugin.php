<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Helper\Stock;

use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddIsInStockFieldToCollection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Adapt addInStockFilterToCollection for multi stocks.
 */
class AdaptAddInStockFilterToCollectionPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AddIsInStockFieldToCollection
     */
    private $addIsInStockFieldToCollection;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddIsInStockFieldToCollection $addIsInStockFieldToCollection
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddIsInStockFieldToCollection $addIsInStockFieldToCollection,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addIsInStockFieldToCollection = $addIsInStockFieldToCollection;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Add filtering by "is in stock" criteria to the stock filter collection when source is not default.
     *
     * @param Stock $subject
     * @param callable $proceed
     * @param Collection $collection
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddInStockFilterToCollection(Stock $subject, callable $proceed, $collection)
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        if ($stockId === $this->defaultStockProvider->getId()) {
            $proceed($collection);
        } else {
            $this->addIsInStockFieldToCollection->execute($collection, $stockId);
        }
    }
}
