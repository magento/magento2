<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\Condition\Sql;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class SQL Builder
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
        '&gt;=' => ':field >= ?',
        '>'     => ':field > ?',
        '&gt;'  => ':field > ?',
        '<='    => ':field <= ?',
        '&lt;=' => ':field <= ?',
        '<'     => ':field < ?',
        '&lt;'  => ':field < ?',
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
     * @param AbstractCollection $collection
     * @param Combine $combine
     * @return $this
     */
    protected function _joinTablesToCollection(
        AbstractCollection $collection,
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
     * Returns sql expression based on rule condition.
     *
     * @param AbstractCondition $condition
     * @param string $value
     * @param bool $isDefaultStoreUsed no longer used because caused an issue about not existing table alias
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getMappedSqlCondition(AbstractCondition $condition, $value = '', $isDefaultStoreUsed = true)
    {
        $argument = $condition->getMappedSqlField();

        // If rule hasn't valid argument - create negative expression to prevent incorrect rule behavior.
        if (empty($argument)) {
            return $this->_expressionFactory->create(['expression' => '1 = -1']);
        }

        $conditionOperator = $condition->getOperatorForValidate();

        if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
        }

        $defaultValue = 0;
        $sql = str_replace(
            ':field',
            $this->_connection->getIfNullSql($this->_connection->quoteIdentifier($argument), $defaultValue),
            $this->_conditionOperatorMap[$conditionOperator]
        );

        $bindValue = $condition->getBindArgumentValue();
        $expression = $value . $this->_connection->quoteInto($sql, $bindValue);

        // values for multiselect attributes can be saved in comma separated format
        // below is a solution for matching such conditions with selected values
        if (in_array($conditionOperator, ['()', '{}']) && is_array($bindValue)) {
            foreach ($bindValue as $item) {
                $expression .= $this->_connection->quoteInto(
                    " OR (FIND_IN_SET (?, {$this->_connection->quoteIdentifier($argument)}) > 0)",
                    $item
                );
            }
        }

        return $this->_expressionFactory->create(
            ['expression' => $expression]
        );
    }

    /**
     * @param Combine $combine
     * @param string $value
     * @param bool $isDefaultStoreUsed
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getMappedSqlCombination(Combine $combine, $value = '', $isDefaultStoreUsed = true)
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
                $out .= $this->_getMappedSqlCombination($condition, $value, $isDefaultStoreUsed);
            } else {
                $out .= $this->_getMappedSqlCondition($condition, $value, $isDefaultStoreUsed);
            }
            $out .=  $out ? (' ' . $con) : '';
        }
        return $this->_expressionFactory->create(['expression' => $out]);
    }

    /**
     * Attach conditions filter to collection
     *
     * @param AbstractCollection $collection
     * @param Combine $combine
     * @return void
     */
    public function attachConditionToCollection(
        AbstractCollection $collection,
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
}
