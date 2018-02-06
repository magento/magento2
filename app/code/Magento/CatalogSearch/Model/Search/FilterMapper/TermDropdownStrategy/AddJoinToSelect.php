<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add joins to select.
 */
class AddJoinToSelect
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ApplyStockCondition
     */
    private $applyStockCondition;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ApplyStockCondition $applyStockCondition
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ApplyStockCondition $applyStockCondition
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->applyStockCondition = $applyStockCondition;
    }

    /**
     * @param int $attributeId
     * @param string $alias
     * @param Select $select
     */
    public function execute(
        int $attributeId,
        string $alias,
        Select $select
    ) {
        $joinCondition = sprintf(
            'search_index.entity_id = %1$s.entity_id AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d',
            $alias,
            $attributeId,
            $this->storeManager->getStore()->getId()
        );
        $select->joinLeft(
            [$alias => $this->resourceConnection->getTableName('catalog_product_index_eav')],
            $joinCondition,
            []
        );
        if ($this->isAddStockFilter()) {
            $stockAlias = $alias . AliasResolver::STOCK_FILTER_SUFFIX;
            $this->applyStockCondition->execute($alias, $stockAlias, $select);
        }
    }

    /**
     * @return bool
     */
    private function isAddStockFilter()
    {
        $isShowOutOfStock = $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );

        return false === $isShowOutOfStock;
    }
}
