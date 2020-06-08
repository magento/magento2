<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Grid;

use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Collection to present grid of customers on admin area
 */
class Collection extends SearchResult
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => ['entity_id' => 'main_table.entity_id']];

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param ResolverInterface $localeResolver
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        ResolverInterface $localeResolver,
        $mainTable = 'customer_grid_flat',
        $resourceModel = Customer::class
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
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
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'billing_region') {
            $conditionSql = $this->_getConditionSql(
                $this->getRegionNameExpresion(),
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
        $fields = $this->getFulltextIndexColumns();
        $whereCondition = '';
        foreach ($fields as $key => $field) {
            $field = $field === 'billing_region'
                ? $this->getRegionNameExpresion()
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
     * Returns list of columns from fulltext index
     *
     * @return array
     */
    private function getFulltextIndexColumns(): array
    {
        $indexes = $this->getConnection()->getIndexList($this->getMainTable());
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                return $index['COLUMNS_LIST'];
            }
        }
        return [];
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
        $regionIdField = $connection->quoteIdentifier('main_table.billing_region_id');
        $localeCondition = $connection->quoteInto("rnt.locale=?", $locale);

        $this->getSelect()
            ->joinLeft(
                ['rct' => $this->getTable('directory_country_region')],
                "rct.region_id={$regionIdField}",
                []
            )->joinLeft(
                ['rnt' => $this->getTable('directory_country_region_name')],
                "rnt.region_id={$regionIdField} AND {$localeCondition}",
                ['billing_region' => $this->getRegionNameExpresion()]
            );

        return $this;
    }

    /**
     * Get SQL Expresion to define Region Name field by locale
     *
     * @return \Zend_Db_Expr
     */
    private function getRegionNameExpresion(): \Zend_Db_Expr
    {
        $connection = $this->getConnection();
        $defaultNameExpr = $connection->getIfNullSql(
            $connection->quoteIdentifier('rct.default_name'),
            $connection->quoteIdentifier('main_table.billing_region')
        );

        return $connection->getIfNullSql(
            $connection->quoteIdentifier('rnt.name'),
            $defaultNameExpr
        );
    }
}
