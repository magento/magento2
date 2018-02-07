<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Eav\Model\Config as EavConfig;

/**
 * This strategy handles static attributes.
 */
class StaticAttributeStrategy implements FilterStrategyInterface
{
    /**
     * Resource connection.
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * Resolving table alias for Search Request filter.
     *
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * Eav attributes config.
     *
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
        $select->joinInner(
            [$alias => $attribute->getBackendTable()],
            'search_index.entity_id = '
            . $this->resourceConnection->getConnection()->quoteIdentifier("$alias.entity_id"),
            []
        );

        return true;
    }

    /**
     * Returns attribute by attribute_code.
     *
     * @param string $field
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }
}
