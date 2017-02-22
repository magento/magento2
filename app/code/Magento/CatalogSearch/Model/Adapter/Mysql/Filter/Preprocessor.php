<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Filter;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\PreprocessorInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Preprocessor implements PreprocessorInterface
{
    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var string
     */
    private $attributePrefix;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var TableMapper
     */
    private $tableMapper;

    /**
     * @param ConditionManager $conditionManager
     * @param ScopeResolverInterface $scopeResolver
     * @param Config $config
     * @param ResourceConnection $resource
     * @param TableMapper $tableMapper
     * @param string $attributePrefix
     */
    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $scopeResolver,
        Config $config,
        ResourceConnection $resource,
        TableMapper $tableMapper,
        $attributePrefix
    ) {
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
        $this->config = $config;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->attributePrefix = $attributePrefix;
        $this->tableMapper = $tableMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(FilterInterface $filter, $isNegation, $query)
    {
        return $this->processQueryWithField($filter, $isNegation, $query);
    }

    /**
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     */
    private function processQueryWithField(FilterInterface $filter, $isNegation, $query)
    {
        /** @var Attribute $attribute */
        $attribute = $this->config->getAttribute(Product::ENTITY, $filter->getField());
        if ($filter->getField() === 'price') {
            $resultQuery = str_replace(
                $this->connection->quoteIdentifier('price'),
                $this->connection->quoteIdentifier('price_index.min_price'),
                $query
            );
        } elseif ($filter->getField() === 'category_ids') {
            return 'category_ids_index.category_id = ' . (int) $filter->getValue();
        } elseif ($attribute->isStatic()) {
            $alias = $this->tableMapper->getMappingAlias($filter);
            $resultQuery = str_replace(
                $this->connection->quoteIdentifier($attribute->getAttributeCode()),
                $this->connection->quoteIdentifier($alias . '.' . $attribute->getAttributeCode()),
                $query
            );
        } elseif (
            $filter->getType() === FilterInterface::TYPE_TERM &&
            in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
        ) {
            $resultQuery = $this->processTermSelect($filter, $isNegation);
        } elseif (
            $filter->getType() === FilterInterface::TYPE_RANGE &&
            in_array($attribute->getBackendType(), ['decimal', 'int'], true)
        ) {
            $resultQuery = $this->processRangeNumeric($filter, $query, $attribute);
        } else {
            $table = $attribute->getBackendTable();
            $select = $this->connection->select();
            $ifNullCondition = $this->connection->getIfNullSql('current_store.value', 'main_table.value');

            $currentStoreId = $this->scopeResolver->getScope()->getId();

            $select->from(['main_table' => $table], 'entity_id')
                ->joinLeft(
                    ['current_store' => $table],
                    'current_store.attribute_id = main_table.attribute_id AND current_store.store_id = '
                    . $currentStoreId,
                    null
                )
                ->columns([$filter->getField() => $ifNullCondition])
                ->where(
                    'main_table.attribute_id = ?',
                    $attribute->getAttributeId()
                )
                ->where('main_table.store_id = ?', Store::DEFAULT_STORE_ID)
                ->having($query);

            $resultQuery = 'search_index.entity_id IN (
                select entity_id from  ' . $this->conditionManager->wrapBrackets($select) . ' as filter
            )';
        }

        return $resultQuery;
    }

    /**
     * @param FilterInterface $filter
     * @param string $query
     * @param Attribute $attribute
     * @return string
     */
    private function processRangeNumeric(FilterInterface $filter, $query, $attribute)
    {
        $tableSuffix = $attribute->getBackendType() === 'decimal' ? '_decimal' : '';
        $table = $this->resource->getTableName("catalog_product_index_eav{$tableSuffix}");
        $select = $this->connection->select();

        $currentStoreId = $this->scopeResolver->getScope()->getId();

        $select->from(['main_table' => $table], 'entity_id')
            ->columns([$filter->getField() => 'main_table.value'])
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ?', $currentStoreId)
            ->having($query);

        $resultQuery = 'search_index.entity_id IN (
                select entity_id from  ' . $this->conditionManager->wrapBrackets($select) . ' as filter
            )';

        return $resultQuery;
    }

    /**
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    private function processTermSelect(FilterInterface $filter, $isNegation)
    {
        $alias = $this->tableMapper->getMappingAlias($filter);
        if (is_array($filter->getValue())) {
            $value = sprintf(
                '%s IN (%s)',
                ($isNegation ? 'NOT' : ''),
                implode(',', array_map([$this->connection, 'quote'], $filter->getValue()))
            );
        } else {
            $value = ($isNegation ? '!' : '') . '= ' . $this->connection->quote($filter->getValue());
        }
        $resultQuery = sprintf(
            '%1$s.value %2$s',
            $alias,
            $value
        );

        return $resultQuery;
    }
}
