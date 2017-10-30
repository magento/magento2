<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\Condition\Sql;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product;

/**
 * Class SQL Builder
 *
 * @package Magento\Rule\Model\Condition\Sql
 */
class Builder
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var array
     */
    protected $_conditionOperatorMap = [
        '=='    => ':field = ?',
        '!='    => ':field <> ?',
        '>='    => ':field >= ?',
        '>'     => ':field > ?',
        '<='    => ':field <= ?',
        '<'     => ':field < ?',
        '{}'    => ':field IN (?)',
        '!{}'   => ':field NOT IN (?)',
        '()'    => ':field IN (?)',
        '!()'   => ':field NOT IN (?)',
    ];

    /**
     * @var \Magento\Rule\Model\Condition\Sql\ExpressionFactory
     */
    protected $_expressionFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param ExpressionFactory $expressionFactory
     * @param AttributeRepositoryInterface|null $attributeRepository
     */
    public function __construct(
        ExpressionFactory $expressionFactory,
        AttributeRepositoryInterface $attributeRepository = null
    ) {
        $this->_expressionFactory = $expressionFactory;
        $this->attributeRepository = $attributeRepository ?:
            ObjectManager::getInstance()->get(AttributeRepositoryInterface::class);
    }

    /**
     * Get tables to join for given conditions combination
     *
     * @param Combine $combine
     * @return array
     */
    protected function _getCombineTablesToJoin(Combine $combine)
    {
        $tables = $this->_getChildCombineTablesToJoin($combine);
        return $tables;
    }

    /**
     * Get child for given conditions combination
     *
     * @param Combine $combine
     * @param array $tables
     * @return array
     */
    protected function _getChildCombineTablesToJoin(Combine $combine, $tables = [])
    {
        foreach ($combine->getConditions() as $condition) {
            if ($condition->getConditions()) {
                $tables = $this->_getChildCombineTablesToJoin($condition);
            } else {
                /** @var $condition AbstractCondition */
                foreach ($condition->getTablesToJoin() as $alias => $table) {
                    if (!isset($tables[$alias])) {
                        $tables[$alias] = $table;
                    }
                }
            }
        }
        return $tables;
    }

    /**
     * Join tables from conditions combination to collection
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param Combine $combine
     * @return $this
     */
    protected function _joinTablesToCollection(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection,
        Combine $combine
    ) {
        foreach ($this->_getCombineTablesToJoin($combine) as $alias => $joinTable) {
            /** @var $condition AbstractCondition */
            $collection->getSelect()->joinLeft(
                [$alias => $collection->getResource()->getTable($joinTable['name'])],
                $joinTable['condition'],
                isset($joinTable['columns']) ? $joinTable['columns'] : '*'
            );
        }
        return $this;
    }

    /**
     * @param AbstractCondition $condition
     * @param string $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMappedSqlCondition(AbstractCondition $condition, $value = '')
    {
        $argument = $condition->getMappedSqlField();
        $isAttributeEavAndNotGlobal = $this->isAttributeEavAndNotGlobal($condition);
        if ($argument) {
            $conditionOperator = $condition->getOperatorForValidate();

            if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
            }

            if ($isAttributeEavAndNotGlobal) {
                $attribute = $this->attributeRepository->get(Product::ENTITY, $condition->getAttribute());
                $arguments = [
                    $argument,
                    'at_' . $attribute->getAttributeCode() . '_default.value'
                ];
                foreach ($arguments as $field) {
                    $fields[] = $this->_connection->quoteIdentifier($field);
                }
                $sql = str_replace(
                    ':field',
                    $this->_connection->getIfNullSql($fields[0], $fields[1]),
                    $this->_conditionOperatorMap[$conditionOperator]
                );
            } else {
                $sql = str_replace(
                    ':field',
                    $this->_connection->getIfNullSql($this->_connection->quoteIdentifier($argument)),
                    $this->_conditionOperatorMap[$conditionOperator]
                );
            }

            return $this->_expressionFactory->create(
                ['expression' => $value . $this->_connection->quoteInto($sql, $condition->getBindArgumentValue())]
            );
        }
        return '';
    }

    /**
     * @param Combine $combine
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getMappedSqlCombination(Combine $combine, $value = '')
    {
        $out = (!empty($value) ? $value : '');
        $value = ($combine->getValue() ? '' : ' NOT ');
        $getAggregator = $combine->getAggregator();
        $conditions = $combine->getConditions();
        foreach ($conditions as $key => $condition) {
            /** @var $condition AbstractCondition|Combine */
            $con = ($getAggregator == 'any' ? Select::SQL_OR : Select::SQL_AND);
            $con = (isset($conditions[$key+1]) ? $con : '');
            if ($condition instanceof Combine) {
                $out .= $this->_getMappedSqlCombination($condition, $value);
            } else {
                $out .= $this->_getMappedSqlCondition($condition, $value);
            }
            $out .=  $out ? (' ' . $con) : '';
        }
        return $this->_expressionFactory->create(['expression' => $out]);
    }

    /**
     * Attach conditions filter to collection
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param Combine $combine
     *
     * @return void
     */
    public function attachConditionToCollection(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection,
        Combine $combine
    ) {
        $this->_connection = $collection->getResource()->getConnection();
        $this->_joinTablesToCollection($collection, $combine);
        $whereExpression = (string)$this->_getMappedSqlCombination($combine);
        if (!empty($whereExpression)) {
            // Select ::where method adds braces even on empty expression
            $collection->getSelect()->where($whereExpression);
        }
    }

    /**
     * Check if attribute is eav and not global
     *
     * @param AbstractCondition $condition
     * @return bool
     * @throws LocalizedException
     */
    private function isAttributeEavAndNotGlobal(AbstractCondition $condition)
    {
        try {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $condition->getAttribute());
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Attribute %1 doesn\'t exist', $condition->getAttribute()));
        }
        return $attribute->getAttributeId() && !$attribute->isScopeGlobal();
    }
}
