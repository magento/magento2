<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Resource\Stock\Item;

use Magento\Framework\DB\GenericMapper;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Data\ObjectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;

/**
 * Interface StockItemCriteriaMapper
 * @package Magento\CatalogInventory\Model\Resource\Stock\Status
 */
class StockItemCriteriaMapper extends GenericMapper
{
    /**
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ObjectFactory $objectFactory
     * @param StoreManagerInterface $storeManager
     * @param MapperFactory $mapperFactory
     * @param Select $select
     */
    public function __construct(
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ObjectFactory $objectFactory,
        MapperFactory $mapperFactory,
        StoreManagerInterface $storeManager,
        Select $select = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($logger, $fetchStrategy, $objectFactory, $mapperFactory, $select);
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->initResource('Magento\CatalogInventory\Model\Resource\Stock\Item');
        $this->map['qty'] = ['main_table', 'qty', 'qty'];
    }

    /**
     * @inheritdoc
     */
    public function mapInitialCondition()
    {
        $this->getSelect()->join(
            ['cp_table' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = cp_table.entity_id',
            ['type_id']
        );
    }

    /**
     * @inheritdoc
     */
    public function mapStockFilter($stock)
    {
        if ($stock instanceof \Magento\CatalogInventory\Api\Data\StockInterface) {
            $stock = $stock->getId();
        }
        $this->addFieldToFilter('main_table.stock_id', $stock);
    }

    /**
     * @inheritdoc
     */
    public function mapWebsiteFilter($website)
    {
        if ($website instanceof \Magento\Store\Model\Website) {
            $website = $website->getId();
        }
        $this->addFieldToFilter('main_table.website_id', $website);
    }

    /**
     * @inheritdoc
     */
    public function mapProductsFilter($products)
    {
        $productIds = [];
        if (!is_array($products)) {
            $products = [$products];
        }
        foreach ($products as $product) {
            if ($product instanceof \Magento\Catalog\Model\Product) {
                $productIds[] = $product->getId();
            } else {
                $productIds[] = $product;
            }
        }
        if (empty($productIds)) {
            $productIds[] = false;
        }
        $this->addFieldToFilter('main_table.product_id', ['in' => $productIds]);
    }

    /**
     * @inheritdoc
     */
    public function mapStockStatus($storeId = null)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $this->getSelect()->joinLeft(
            ['status_table' => $this->getTable('cataloginventory_stock_status')],
            'main_table.product_id=status_table.product_id' .
            ' AND main_table.stock_id=status_table.stock_id' .
            $this->getConnection()->quoteInto(
                ' AND status_table.website_id=?',
                $websiteId
            ),
            ['stock_status']
        );
    }

    /**
     * @inheritdoc
     */
    public function mapManagedFilter($isStockManagedInConfig)
    {
        if ($isStockManagedInConfig) {
            $this->getSelect()->where('(manage_stock = 1 OR use_config_manage_stock = 1)');
        } else {
            $this->addFieldToFilter('manage_stock', 1);
        }
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Model\Exception
     */
    public function mapQtyFilter($comparisonMethod, $qty)
    {
        $methods = ['<' => 'lt', '>' => 'gt', '=' => 'eq', '<=' => 'lteq', '>=' => 'gteq', '<>' => 'neq'];
        if (!isset($methods[$comparisonMethod])) {
            throw new \Magento\Framework\Model\Exception(
                __('%1 is not a correct comparison method.', $comparisonMethod)
            );
        }
        $this->addFieldToFilter('main_table.qty', [$methods[$comparisonMethod] => $qty]);
    }
}
