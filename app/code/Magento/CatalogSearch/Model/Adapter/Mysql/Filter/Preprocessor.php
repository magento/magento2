<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Filter;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Search\FilterMapper\VisibilityFilter;
use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\Filter\PreprocessorInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Store\Model\Store;

/**
 * ElasticSearch search filter pre-processor.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated
 * @see \Magento\ElasticSearch
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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param ConditionManager $conditionManager
     * @param ScopeResolverInterface $scopeResolver
     * @param Config $config
     * @param ResourceConnection $resource
     * @param TableMapper $tableMapper
     * @param string $attributePrefix
     * @param ScopeConfigInterface|null $scopeConfig
     * @param AliasResolver|null $aliasResolver
     * @param Session $customerSession
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ConditionManager $conditionManager,
        ScopeResolverInterface $scopeResolver,
        Config $config,
        ResourceConnection $resource,
        TableMapper $tableMapper,
        $attributePrefix,
        ScopeConfigInterface $scopeConfig = null,
        AliasResolver $aliasResolver = null,
        Session $customerSession = null
    ) {
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
        $this->config = $config;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->attributePrefix = $attributePrefix;

        if (null === $scopeConfig) {
            $scopeConfig = ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        }
        if (null === $aliasResolver) {
            $aliasResolver = ObjectManager::getInstance()->get(AliasResolver::class);
        }
        if (null === $customerSession) {
            $customerSession = ObjectManager::getInstance()->get(Session::class);
        }

        $this->scopeConfig = $scopeConfig;
        $this->aliasResolver = $aliasResolver;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function process(FilterInterface $filter, $isNegation, $query)
    {
        return $this->processQueryWithField($filter, $isNegation, $query);
    }

    /**
     * Process query with field.
     *
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processQueryWithField(FilterInterface $filter, $isNegation, $query)
    {
        /** @var Attribute $attribute */
        $attribute = $this->config->getAttribute(Product::ENTITY, $filter->getField());
        $linkIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        if ($filter->getField() === 'price') {
            $resultQuery = str_replace(
                $this->connection->quoteIdentifier('price'),
                $this->connection->quoteIdentifier('price_index.min_price'),
                $query
            );

            $resultQuery .= sprintf(
                ' AND %s = %s',
                $this->connection->quoteIdentifier('price_index.customer_group_id'),
                $this->customerSession->getCustomerGroupId()
            );
        } elseif ($filter->getField() === 'category_ids') {
            return $this->connection->quoteInto(
                'category_ids_index.category_id in (?)',
                $filter->getValue()
            );
        } elseif ($attribute->isStatic()) {
            $alias = $this->aliasResolver->getAlias($filter);
            $resultQuery = str_replace(
                $this->connection->quoteIdentifier($attribute->getAttributeCode()),
                $this->connection->quoteIdentifier($alias . '.' . $attribute->getAttributeCode()),
                $query
            );
        } elseif ($filter->getField() === VisibilityFilter::VISIBILITY_FILTER_FIELD) {
            return '';
        } elseif ($filter->getType() === FilterInterface::TYPE_TERM &&
            in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'], true)
        ) {
            $resultQuery = $this->processTermSelect($filter, $isNegation);
        } elseif ($filter->getType() === FilterInterface::TYPE_RANGE &&
            in_array($attribute->getBackendType(), ['decimal', 'int'], true)
        ) {
            $resultQuery = $this->processRangeNumeric($filter, $query, $attribute);
        } else {
            $table = $attribute->getBackendTable();
            $select = $this->connection->select();
            $ifNullCondition = $this->connection->getIfNullSql('current_store.value', 'main_table.value');

            $currentStoreId = $this->scopeResolver->getScope()->getId();

            $select->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['entity_id'])
                ->join(
                    ['main_table' => $table],
                    "main_table.{$linkIdField} = e.{$linkIdField}",
                    []
                )
                ->joinLeft(
                    ['current_store' => $table],
                    "current_store.{$linkIdField} = main_table.{$linkIdField} AND "
                        . "current_store.attribute_id = main_table.attribute_id AND current_store.store_id = "
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

            $resultQuery = 'search_index.entity_id IN ('
                . 'select entity_id from  '
                . $this->conditionManager->wrapBrackets($select)
                . ' as filter)';
        }

        return $resultQuery;
    }

    /**
     * Process range numeric.
     *
     * @param FilterInterface $filter
     * @param string $query
     * @param Attribute $attribute
     * @return string
     * @throws \Exception
     */
    private function processRangeNumeric(FilterInterface $filter, $query, $attribute)
    {
        $tableSuffix = $attribute->getBackendType() === 'decimal' ? '_decimal' : '';
        $table = $this->resource->getTableName("catalog_product_index_eav{$tableSuffix}");
        $select = $this->connection->select();
        $entityField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getIdentifierField();

        $currentStoreId = $this->scopeResolver->getScope()->getId();

        $select->from(['e' => $this->resource->getTableName('catalog_product_entity')], ['entity_id'])
            ->join(
                ['main_table' => $table],
                "main_table.{$entityField} = e.{$entityField}",
                []
            )
            ->columns([$filter->getField() => 'main_table.value'])
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ?', $currentStoreId)
            ->having($query);

        $resultQuery = 'search_index.entity_id IN ('
            . 'select entity_id from  '
            . $this->conditionManager->wrapBrackets($select)
            . ' as filter)';

        return $resultQuery;
    }

    /**
     * Process term select.
     *
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    private function processTermSelect(FilterInterface $filter, $isNegation)
    {
        $alias = $this->aliasResolver->getAlias($filter);
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

    /**
     * Get product metadata pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    protected function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
