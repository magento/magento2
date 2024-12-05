<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Address\Grid;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

/**
 * Class getting collection of addresses assigned to customer
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * List of fields to fulltext search
     */
    private const FIELDS_TO_FULLTEXT_SEARCH = [
        'firstname',
        'lastname',
        'street',
        'city',
        'region',
        'postcode',
        'telephone',
    ];

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param ResolverInterface $localeResolver
     * @param string $model
     * @param AdapterInterface|string|null $connection
     * @param AbstractDb $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        ResolverInterface $localeResolver,
        $model = Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_idFieldName = 'entity_id';
        $this->localeResolver = $localeResolver;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->_idFieldName = 'entity_id';
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinRegionNameTable();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @inheritdoc
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'region') {
            $conditionSql = $this->_getConditionSql(
                $this->getRegionNameExpression(),
                $condition
            );
            $this->getSelect()->where($conditionSql);
            return $this;
        }

        if (is_string($field) && count(explode('.', $field)) === 1) {
            $field = 'main_table.' . $field;
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add fulltext filter
     *
     * @param string $value
     * @return $this
     */
    public function addFullTextFilter(string $value)
    {
        $fields = self::FIELDS_TO_FULLTEXT_SEARCH;
        $whereCondition = '';
        foreach ($fields as $key => $field) {
            $field = $field === 'region'
                ? $this->getRegionNameExpression()
                : 'main_table.' . $field;
            $condition = $this->_getConditionSql(
                $this->getConnection()->quoteIdentifier($field),
                ['like' => "%$value%"]
            );
            $whereCondition .= ($key === 0 ? '' : ' OR ') . $condition;
        }
        if ($whereCondition) {
            $this->getSelect()->where($whereCondition);
        }

        return $this;
    }

    /**
     * Join region name table by current locale
     *
     * @return $this
     */
    private function joinRegionNameTable()
    {
        $locale = $this->localeResolver->getLocale();
        $connection = $this->getConnection();
        $regionIdField = $connection->quoteIdentifier('main_table.region_id');
        $localeCondition = $connection->quoteInto("rnt.locale=?", $locale);

        $this->getSelect()
            ->joinLeft(
                ['rct' => $this->getTable('directory_country_region')],
                "rct.region_id={$regionIdField}",
                []
            )->joinLeft(
                ['rnt' => $this->getTable('directory_country_region_name')],
                "rnt.region_id={$regionIdField} AND {$localeCondition}",
                ['region' => $this->getRegionNameExpression()]
            );

        return $this;
    }

    /**
     * Get SQL Expression to define Region Name field by locale
     *
     * @return \Zend_Db_Expr
     */
    private function getRegionNameExpression(): \Zend_Db_Expr
    {
        $connection = $this->getConnection();
        $defaultNameExpr = $connection->getIfNullSql(
            $connection->quoteIdentifier('rct.default_name'),
            $connection->quoteIdentifier('main_table.region')
        );

        return $connection->getIfNullSql(
            $connection->quoteIdentifier('rnt.name'),
            $defaultNameExpr
        );
    }
}
