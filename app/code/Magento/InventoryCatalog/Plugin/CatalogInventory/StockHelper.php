<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Class provides around Plugin on Magento\CatalogInventory\Helper\Stock::addInStockFilterToCollection
 */
class StockHelper
{

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        ScopeConfigInterface $scopeConfig,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->scopeConfig = $scopeConfig;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
    }

    /**
     * Plugin to use multi stocks for filtering the collection to return only in stock products.
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

        $stockTable = $this->stockIndexTableNameResolver->execute($stockId);
        $condition = $this->getFilterCondition();

        $collection->getSelect()->join(
            ['inventory_in_stock' => $stockTable],
            'e.sku = inventory_in_stock.sku',
            []
        )->where('inventory_in_stock.' . IndexStructure::QUANTITY . $condition);
    }

    /**
     * Return the filter condition by config setting manage stock.
     *
     * @return string
     */
    private function getFilterCondition(): string
    {
        $manageStock = (bool)$this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $condition = ' > 0';

        if ($manageStock === false) {
            $condition = ' >= 0';
        }
        return $condition;
    }
}
