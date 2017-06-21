<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Indexer\Model\ResourceModel\FrontendResource;

/**
 * Class VisibilityFilter
 * Applies filter by visibility to base select
 */
class VisibilityFilter
{
    /**
     * Defines strategies of how filter should be applied
     */
    const FILTER_BY_JOIN = 'join_filter';
    const FILTER_BY_WHERE = 'where_filter';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var FrontendResource
     */
    private $indexerEavFrontendResource;

    /**
     * @param ConditionManager $conditionManager
     * @param StoreManagerInterface $storeManager
     * @param EavConfig $eavConfig
     * @param FrontendResource $indexerEavFrontendResource
     */
    public function __construct(
        ConditionManager $conditionManager,
        StoreManagerInterface $storeManager,
        EavConfig $eavConfig,
        FrontendResource $indexerEavFrontendResource
    ) {
        $this->conditionManager = $conditionManager;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
        $this->indexerEavFrontendResource = $indexerEavFrontendResource;
    }

    /**
     * Applies visibility filter through join or where condition
     *
     * @param Select $select
     * @param FilterInterface $filter
     * @param string $type
     * @return Select
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(Select $select, FilterInterface $filter, $type)
    {
        if ($type !== self::FILTER_BY_JOIN && $type !== self::FILTER_BY_WHERE) {
            throw new \InvalidArgumentException(sprintf('Invalid filter type: %s', $type));
        }

        $select = clone $select;

        $type === self::FILTER_BY_JOIN
            ? $this->applyFilterByJoin($filter, $select)
            : $this->applyFilterByWhere($filter, $select);

        return $select;
    }

    /**
     * Applies filter by visibility as inner join
     *
     * @param Select $select
     * @param FilterInterface $filter
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyFilterByJoin(FilterInterface $filter, Select $select)
    {
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            ['visibility_filter' => $this->indexerEavFrontendResource->getMainTable()],
            $this->conditionManager->combineQueries(
                [
                    sprintf('%s.entity_id = visibility_filter.entity_id', $mainTableAlias),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.attribute_id',
                        '=',
                        $this->getVisibilityAttributeId()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.value',
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        'visibility_filter.store_id',
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            ),
            []
        );
    }

    /**
     * Applies filter by visibility as where condition
     *
     * @param Select $select
     * @param FilterInterface $filter
     * @return void
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function applyFilterByWhere(FilterInterface $filter, Select $select)
    {
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->where(
            $this->conditionManager->combineQueries(
                [
                    $this->conditionManager->generateCondition(
                        sprintf('%s.attribute_id', $mainTableAlias),
                        '=',
                        $this->getVisibilityAttributeId()
                    ),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.value', $mainTableAlias),
                        is_array($filter->getValue()) ? 'in' : '=',
                        $filter->getValue()
                    ),
                    $this->conditionManager->generateCondition(
                        sprintf('%s.store_id', $mainTableAlias),
                        '=',
                        $this->storeManager->getStore()->getId()
                    ),
                ],
                Select::SQL_AND
            )
        );
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     */
    private function getVisibilityAttributeId()
    {
        $attr = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'visibility');

        if ($attr === null) {
            throw new \InvalidArgumentException('Wrong id for visibility attribute');
        }

        return (int) $attr->getId();
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param Select $select
     * @return string|null
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
