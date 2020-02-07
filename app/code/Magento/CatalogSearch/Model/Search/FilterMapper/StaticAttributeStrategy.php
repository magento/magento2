<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\DB\Select;

/**
 * This strategy handles static attributes
 *
 * @deprecated 101.0.0
 * @see \Magento\ElasticSearch
 */
class StaticAttributeStrategy implements FilterStrategyInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param AliasResolver $aliasResolver
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        AliasResolver $aliasResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->aliasResolver = $aliasResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $attribute = $this->getAttributeByCode($filter->getField());
        $alias = $this->aliasResolver->getAlias($filter);
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [$alias => $attribute->getBackendTable()],
            sprintf('%s.entity_id = ', $mainTableAlias)
            . $this->resourceConnection->getConnection()->quoteIdentifier("$alias.entity_id"),
            []
        );

        return true;
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
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
