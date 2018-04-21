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
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AddIsInStockFieldToCollection $addIsInStockFieldToCollection
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AddIsInStockFieldToCollection $addIsInStockFieldToCollection
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->addIsInStockFieldToCollection = $addIsInStockFieldToCollection;
    }

    /**
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
        $this->addIsInStockFieldToCollection->execute($collection, $stockId);
    }
}
