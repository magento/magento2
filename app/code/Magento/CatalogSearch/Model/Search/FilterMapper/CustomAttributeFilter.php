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
 * Class CustomAttributeFilter
 * Applies filters by custom attributes to base select
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
     * @param FilterInterface[] ...$filters
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function apply(Select $select, FilterInterface ... $filters)
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

            $joinConditions = $this->createQueryConditions($attributeId, $filterJoinAlias);

            array_unshift(
                $joinConditions,
                sprintf('%s.entity_id = %s.entity_id', $mainTableAlias, $filterJoinAlias),
                sprintf('%s.source_id = %s.source_id', $mainTableAlias, $filterJoinAlias)
            );

            $select->joinInner(
                [$filterJoinAlias => $this->resourceConnection->getTableName('catalog_product_index_eav')],
                $this->conditionManager->combineQueries($joinConditions, Select::SQL_AND),
                []
            );
        }

        if (count($attributes) === 1) {
            // forces usage of PRIMARY key in main table
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
     * @param int $attributeId
     * @param string $mainTableAlias
     * @return array
     */
    private function createQueryConditions($attributeId, $mainTableAlias)
    {
        return [
            $this->conditionManager->generateCondition(
                sprintf('%s.attribute_id', $mainTableAlias),
                '=',
                $attributeId
            ),
            $this->conditionManager->generateCondition(
                sprintf('%s.store_id', $mainTableAlias),
                '=',
                (int) $this->storeManager->getStore()->getId()
            )
        ];
    }

    /**
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
