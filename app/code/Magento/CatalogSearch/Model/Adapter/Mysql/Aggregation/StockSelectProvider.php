<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\CatalogInventory\Model\Stock;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;

class StockSelectProvider implements StockSelectProviderInterface
{

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @inheritdoc
     */
    public function getSelect(int $currentScope, AbstractAttribute $attribute, Select $select): Select
    {
        $connection = $this->resource->getConnection();
        $currentScopeId = $this->scopeResolver->getScope($currentScope)
            ->getId();
        $table = $this->resource->getTableName(
            'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
        );
        $subSelect = $select;
        $subSelect->from(['main_table' => $table], ['main_table.entity_id', 'main_table.value'])
            ->distinct()
            ->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'main_table.source_id = stock_index.product_id',
                []
            )
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ? ', $currentScopeId)
            ->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);
        $parentSelect = $connection->select();
        $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
        $select = $parentSelect;
        return $select;
    }
}
