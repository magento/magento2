<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\ObjectFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\DataObject;

/**
 * Class AbstractMapper
 * @package Magento\Framework\DB
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     * Resource model name
     *
     * @var string
     */
    protected $resourceModel;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;

    /**
     * Store joined tables here
     *
     * @var array
     */
    protected $joinedTables = [];

    /**
     * DB connection
     *
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * Select object
     *
     * @var Select
     */
    protected $select;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var FetchStrategyInterface
     */
    protected $fetchStrategy;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var MapperFactory
     */
    protected $mapperFactory;

    /**
     * Fields and filters map
     *
     * @var array
     */
    protected $map = [];

    /**
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ObjectFactory $objectFactory
     * @param MapperFactory $mapperFactory
     * @param Select $select
     */
    public function __construct(
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ObjectFactory $objectFactory,
        MapperFactory $mapperFactory,
        Select $select = null
    ) {
        $this->logger = $logger;
        $this->fetchStrategy = $fetchStrategy;
        $this->objectFactory = $objectFactory;
        $this->mapperFactory = $mapperFactory;
        $this->select = $select;
        $this->init();
    }

    /**
     * Set initial conditions
     *
     * @return void
     */
    abstract protected function init();

    /**
     * Map criteria to Select Query Object
     *
     * @param CriteriaInterface $criteria
     * @return Select
     */
    public function map(CriteriaInterface $criteria)
    {
        $criteriaParts = $criteria->toArray();
        foreach ($criteriaParts as $key => $value) {
            $camelCaseKey = \Magento\Framework\Api\SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);
            $mapperMethod = 'map' . $camelCaseKey;
            if (method_exists($this, $mapperMethod)) {
                if (!is_array($value)) {
                    $value = [$value];
                }
                call_user_func_array([$this, $mapperMethod], $value);
            }
        }
        return $this->select;
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param array|string $fields
     * @return void
     */
    public function addExpressionFieldToSelect($alias, $expression, $fields)
    {
        // validate alias
        if (!is_array($fields)) {
            $fields = [$fields => $fields];
        }
        $fullExpression = $expression;
        foreach ($fields as $fieldKey => $fieldItem) {
            $fullExpression = str_replace('{{' . $fieldKey . '}}', $fieldItem, $fullExpression);
        }
        $this->getSelect()->columns([$alias => $fullExpression]);
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $conditions[] = $this->translateCondition($value, isset($condition[$key]) ? $condition[$key] : null);
            }

            $resultCondition = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $resultCondition = $this->translateCondition($field, $condition);
        }
        $this->select->where($resultCondition, null, Select::TYPE_CONDITION);
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        $this->getSelect()->reset();
    }

    /**
     * Set resource model name
     *
     * @param string $model
     * @return void
     */
    protected function setResourceModelName($model)
    {
        $this->resourceModel = $model;
    }

    /**
     *  Retrieve resource model name
     *
     * @return string
     */
    protected function getResourceModelName()
    {
        return $this->resourceModel;
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource()
    {
        if (empty($this->resource)) {
            $this->resource = \Magento\Framework\App\ObjectManager::getInstance()->create(
                $this->getResourceModelName()
            );
        }
        return $this->resource;
    }

    /**
     * Standard query builder initialization
     *
     * @param string $resourceInterface
     * @return void
     */
    protected function initResource($resourceInterface)
    {
        $this->setResourceModelName($resourceInterface);
        $this->setConnection($this->getResource()->getConnection());
        if (!$this->select) {
            $this->select = $this->getConnection()->select();
            $this->initSelect();
        }
    }

    /**
     * Init collection select
     *
     * @return void
     */
    protected function initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getResource()->getMainTable()]);
    }

    /**
     * Join table to collection select
     *
     * @param string $table
     * @param string $condition
     * @param string $cols
     * @return void
     */
    protected function join($table, $condition, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }
        if (!isset($this->joinedTables[$table])) {
            $this->getSelect()->join([$alias => $this->getTable($table)], $condition, $cols);
            $this->joinedTables[$alias] = true;
        }
    }

    /**
     * Retrieve connection object
     *
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set database connection adapter
     *
     * @param AdapterInterface $connection
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setConnection($connection)
    {
        if (!$connection instanceof \Magento\Framework\DB\Adapter\AdapterInterface) {
            throw new \InvalidArgumentException(
                (string)new \Magento\Framework\Phrase(
                    'dbModel read resource does not implement \Magento\Framework\DB\Adapter\AdapterInterface'
                )
            );
        }
        $this->connection = $connection;
    }

    /**
     * Build sql where condition part
     *
     * @param   string|array $field
     * @param   null|string|array $condition
     * @return  string
     */
    protected function translateCondition($field, $condition)
    {
        $field = $this->getMappedField($field);
        return $this->getConditionSql($this->getConnection()->quoteIdentifier($field), $condition);
    }

    /**
     * Try to get mapped field name for filter to collection
     *
     * @param   string $field
     * @return  string
     */
    protected function getMappedField($field)
    {
        $mapper = $this->getMapper();

        if (isset($mapper['fields'][$field])) {
            $mappedField = $mapper['fields'][$field];
        } else {
            $mappedField = $field;
        }

        return $mappedField;
    }

    /**
     * Retrieve mapper data
     *
     * @return array|bool|null
     */
    protected function getMapper()
    {
        if (isset($this->map)) {
            return $this->map;
        } else {
            return false;
        }
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * - array("from" => $fromValue, "to" => $toValue)
     * - array("eq" => $equalValue)
     * - array("neq" => $notEqualValue)
     * - array("like" => $likeValue)
     * - array("in" => array($inValues))
     * - array("nin" => array($notInValues))
     * - array("notnull" => $valueIsNotNull)
     * - array("null" => $valueIsNull)
     * - array("moreq" => $moreOrEqualValue)
     * - array("gt" => $greaterValue)
     * - array("lt" => $lessValue)
     * - array("gteq" => $greaterOrEqualValue)
     * - array("lteq" => $lessOrEqualValue)
     * - array("finset" => $valueInSet)
     * - array("regexp" => $regularExpression)
     * - array("seq" => $stringValue)
     * - array("sneq" => $stringValue)
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     */
    protected function getConditionSql($fieldName, $condition)
    {
        return $this->getConnection()->prepareSqlCondition($fieldName, $condition);
    }

    /**
     * Return the field name for the condition.
     *
     * @param string $fieldName
     * @return string
     */
    protected function getConditionFieldName($fieldName)
    {
        return $fieldName;
    }

    /**
     * Hook for operations before rendering filters
     * @return void
     */
    protected function renderFiltersBefore()
    {
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->getResource()->getTable($table);
    }

    /**
     * Get \Magento\Framework\DB\Select object instance
     *
     * @return Select
     */
    protected function getSelect()
    {
        return $this->select;
    }
}
