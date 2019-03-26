<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\DB\Select;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Catalog\Model\Product;

/**
 * Applies filters by custom attributes to base select.
 *
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class CustomAttributeFilter
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ConditionManager $conditionManager
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param AliasResolver $aliasResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ConditionManager $conditionManager,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->conditionManager = $conditionManager;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->aliasResolver = $aliasResolver;
    }

    /**
     * Applies filters by custom attributes to base select
     *
     * @param Select $select
     * @param FilterInterface[] $filters
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function apply(Select $select, FilterInterface ...$filters)
    {
        $select = clone $select;
        $mainTableAlias = $this->extractTableAliasFromSelect($select);
        $attributes = [];

        foreach ($filters as $filter) {
            $filterJoinAlias = $this->aliasResolver->getAlias($filter);

            $attributeId = $this->getAttributeIdByCode($filter->getField());

            if ($attributeId === null) {
                throw new \InvalidArgumentException(
                    sprintf('Invalid attribute id for field: %s', $filter->getField())
                );
            }

            $attributes[] = $attributeId;

            $select->joinInner(
                [$filterJoinAlias => $this->resourceConnection->getTableName('catalog_product_index_eav')],
                $this->conditionManager->combineQueries(
                    $this->getJoinConditions($attributeId, $mainTableAlias, $filterJoinAlias),
                    Select::SQL_AND
                ),
                []
            );
        }

        if (count($attributes) === 1) {
            // forces usage of PRIMARY key in main table
            // is required to boost performance in case when we have just one filter by custom attribute
            $attribute = reset($attributes);
            $filter = reset($filters);
            $select->where(
                $this->conditionManager->generateCondition(
                    sprintf('%s.attribute_id', $mainTableAlias),
                    '=',
                    $attribute
                )
            )->where(
                $this->conditionManager->generateCondition(
                    sprintf('%s.value', $mainTableAlias),
                    is_array($filter->getValue()) ? 'in' : '=',
                    $filter->getValue()
                )
            );
        }

        return $select;
    }

    /**
     * Returns Joins conditions for table catalog_product_index_eav
     *
     * @param int $attrId
     * @param string $mainTable
     * @param string $joinTable
     * @return array
     */
    private function getJoinConditions($attrId, $mainTable, $joinTable)
    {
        return [
            sprintf('`%s`.`entity_id` = `%s`.`entity_id`', $mainTable, $joinTable),
            $this->conditionManager->generateCondition(
                sprintf('%s.attribute_id', $joinTable),
                '=',
                $attrId
            ),
            $this->conditionManager->generateCondition(
                sprintf('%s.store_id', $joinTable),
                '=',
                (int) $this->storeManager->getStore()->getId()
            )
        ];
    }

    /**
     * Returns attribute id by code
     *
     * @param string $field
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeIdByCode($field)
    {
        $attr = $this->eavConfig->getAttribute(Product::ENTITY, $field);

        return ($attr && $attr->getId()) ? (int) $attr->getId() : null;
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param Select $select
     * @return string|null
     * @throws \Zend_Db_Select_Exception
     */
    private function extractTableAliasFromSelect(Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
