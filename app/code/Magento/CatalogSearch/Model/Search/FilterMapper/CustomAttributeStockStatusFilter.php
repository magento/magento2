<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Add stock status filter for each requested filter
 */
class CustomAttributeStockStatusFilter
{
    /**
     * Suffix to append to filter name in order to generate stock status table alias for JOIN clause
     */
    private const STOCK_STATUS_TABLE_ALIAS_SUFFIX = '_stock_index';
    /**
     * Attribute types to apply
     */
    private const TARGET_ATTRIBUTE_TYPES = [
        'select',
        'multiselect'
    ];
    /**
     * @var EavConfig
     */
    private $eavConfig;
    /**
     * @var AliasResolver
     */
    private $aliasResolver;
    /**
     * @var StockStatusQueryBuilder|null
     */
    private $stockStatusQueryBuilder;

    /**
     * @param EavConfig $eavConfig
     * @param AliasResolver $aliasResolver
     * @param StockStatusQueryBuilder $stockStatusQueryBuilder
     */
    public function __construct(
        EavConfig $eavConfig,
        AliasResolver $aliasResolver,
        StockStatusQueryBuilder $stockStatusQueryBuilder
    ) {
        $this->eavConfig = $eavConfig;
        $this->aliasResolver = $aliasResolver;
        $this->stockStatusQueryBuilder = $stockStatusQueryBuilder;
    }

    /**
     * Apply stock status filter to provided filter
     *
     * @param Select $select
     * @param mixed $values
     * @param FilterInterface[] $filters
     * @return Select
     */
    public function apply(Select $select, $values = null, FilterInterface ...$filters): Select
    {
        $select = clone $select;
        foreach ($filters as $filter) {
            if ($this->isApplicable($filter)) {
                $mainTableAlias = $this->aliasResolver->getAlias($filter);
                $stockTableAlias = $mainTableAlias . self::STOCK_STATUS_TABLE_ALIAS_SUFFIX;
                $select = $this->stockStatusQueryBuilder->apply(
                    $select,
                    $mainTableAlias,
                    $stockTableAlias,
                    'source_id',
                    $values
                );
            }
        }
        return $select;
    }

    /**
     * Check if stock status filter is applicable to provided filter
     *
     * @param FilterInterface $filter
     * @return bool
     */
    private function isApplicable(FilterInterface $filter): bool
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $filter->getField());
        return $attribute
            && $filter->getType() === FilterInterface::TYPE_TERM
            && in_array($attribute->getFrontendInput(), self::TARGET_ATTRIBUTE_TYPES, true);
    }
}
