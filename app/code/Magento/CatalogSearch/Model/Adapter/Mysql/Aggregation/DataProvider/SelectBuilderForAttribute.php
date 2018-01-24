<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;

use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\StockConditionJoiner;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Select;

/**
 * Build select for attribute.
 */
class SelectBuilderForAttribute
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockConditionJoiner
     */
    private $stockConditionJoiner;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param StockConditionJoiner $stockConditionJoiner
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        StockConditionJoiner $stockConditionJoiner
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->stockConditionJoiner = $stockConditionJoiner;
    }

    /**
     * @param Select $select
     * @param AbstractAttribute $attribute
     * @param int $currentScope
     *
     * @return Select
     */
    public function execute(Select $select, AbstractAttribute $attribute, int $currentScope): Select
    {
        $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
        $table = $this->resource->getTableName(
            'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
        );
        $subSelect = $select;
        $subSelect->from(['main_table' => $table], ['main_table.entity_id', 'main_table.value'])
            ->distinct()
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ? ', $currentScopeId);
        $this->stockConditionJoiner->execute($subSelect);

        $parentSelect = $this->resource->getConnection()->select();
        $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);

        return $parentSelect;
    }
}
