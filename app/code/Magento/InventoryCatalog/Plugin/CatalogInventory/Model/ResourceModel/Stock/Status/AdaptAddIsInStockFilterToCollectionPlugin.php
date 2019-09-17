<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryCatalog\Model\ResourceModel\AddIsInStockFilterToCollection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Adapt adding is in stock filter to collection for multi stocks.
 */
class AdaptAddIsInStockFilterToCollectionPlugin
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AddIsInStockFilterToCollection
     */
    private $addIsInStockFilterToCollection;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddIsInStockFilterToCollection $addIsInStockFilterToCollection
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddIsInStockFilterToCollection $addIsInStockFilterToCollection,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addIsInStockFilterToCollection = $addIsInStockFilterToCollection;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * Adapt adding is in stock filter to collection for multi stocks.
     *
     * @param Status $stockStatus
     * @param callable $proceed
     * @param Collection $collection
     * @return Status
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddIsInStockFilterToCollection(
        Status $stockStatus,
        callable $proceed,
        $collection
    ) {
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        if ($this->defaultStockProvider->getId() === $stockId) {
            return $proceed($collection);
        } else {
            $this->addIsInStockFilterToCollection->execute($collection, $stockId);
        }

        return $stockStatus;
    }
}
