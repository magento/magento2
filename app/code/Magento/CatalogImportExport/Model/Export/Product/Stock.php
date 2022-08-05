<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Export\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Configuration;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResourceModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Stock status collection filter
 */
class Stock
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var StockItemResourceModel
     */
    private $stockItemResourceModel;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StockItemResourceModel $stockItemResourceModel
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StockItemResourceModel $stockItemResourceModel
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->stockItemResourceModel = $stockItemResourceModel;
    }

    /**
     * Filter provided collection to return only "in stock" products
     *
     * @param Collection $collection
     * @return Collection
     */
    public function addInStockFilterToCollection(Collection $collection): Collection
    {
        $manageStock = $this->scopeConfig->getValue(
            Configuration::XML_PATH_MANAGE_STOCK,
            ScopeInterface::SCOPE_STORE
        );
        $cond = [
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
        ];

        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = 1';
        }
        return $this->addFilterToCollection($collection, '(' . join(') OR (', $cond) . ')');
    }

    /**
     * Filter provided collection to return only "out of stock" products
     *
     * @param Collection $collection
     * @return Collection
     */
    public function addOutOfStockFilterToCollection(Collection $collection): Collection
    {
        $manageStock = $this->scopeConfig->getValue(
            Configuration::XML_PATH_MANAGE_STOCK,
            ScopeInterface::SCOPE_STORE
        );
        $cond = [
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=0',
        ];

        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=0';
        }
        return $this->addFilterToCollection($collection, '(' . join(') OR (', $cond) . ')');
    }

    /**
     * Add stock status filter to the collection
     *
     * @param Collection $collection
     * @param string $condition
     * @return Collection
     */
    private function addFilterToCollection(Collection $collection, string $condition): Collection
    {
        $condition = str_replace(
            '{{table}}',
            'inventory_stock_item_filter',
            '({{table}}.product_id=e.entity_id) AND (' . $condition . ')'
        );
        $collection->getSelect()
            ->joinInner(
                ['inventory_stock_item_filter' => $this->stockItemResourceModel->getMainTable()],
                $condition,
                []
            );
        return $collection;
    }
}
