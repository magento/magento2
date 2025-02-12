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
     * @var array
     */
    private $stringConditionOperatorMap = [
        '{}' => ':field LIKE ?',
        '!{}' => ':field NOT LIKE ?',
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
        ?AttributeRepositoryInterface $attributeRepository = null
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
    ): Builder {
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getMappedSqlCondition(
        AbstractCondition $condition,
        string $value = '',
        bool $isDefaultStoreUsed = true
    ): string {
        $argument = $condition->getMappedSqlField();

        // If rule hasn't valid argument - prevent incorrect rule behavior.
        if (empty($argument)) {
            return $this->_expressionFactory->create(['expression' => '1 = -1']);
        } elseif (preg_match('/[^a-z0-9\-_\.\`]/i', $argument) > 0 && !$argument instanceof \Zend_Db_Expr) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid field'));
        }

        $conditionOperator = $condition->getOperatorForValidate();

        if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
        }

        $defaultValue = 0;
        //operator 'contains {}' is mapped to 'IN()' query that cannot work with substrings
        // adding mapping to 'LIKE %%'
        if ($condition->getInputType() === 'string'
            && in_array($conditionOperator, array_keys($this->stringConditionOperatorMap), true)
        ) {
            $sql = str_replace(
                ':field',
                (string) $this->_connection->getIfNullSql(
                    $this->_connection->quoteIdentifier($argument),
                    $defaultValue
                ),
                $this->stringConditionOperatorMap[$conditionOperator]
            );
            $bindValue = $condition->getBindArgumentValue();
            $expression = $value . $this->_connection->quoteInto($sql, "%$bindValue%");
        } else {
            $sql = str_replace(
                ':field',
                (string) $this->_connection->getIfNullSql(
                    $this->_connection->quoteIdentifier($argument),
                    $defaultValue
                ),
                $this->_conditionOperatorMap[$conditionOperator]
            );
            $bindValue = $condition->getBindArgumentValue();
            $expression = $value . $this->_connection->quoteInto($sql, $bindValue);
        }
        // values for multiselect attributes can be saved in comma-separated format
        // below is a solution for matching such conditions with selected values
        if (is_array($bindValue) && \in_array($conditionOperator, ['()', '{}'], true)) {
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
     * Get mapped sql combination.
     *
     * @param Combine $combine
     * @param string $value
     * @param bool $isDefaultStoreUsed
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMappedSqlCombination(
        Combine $combine,
        string $value = '',
        bool $isDefaultStoreUsed = true
    ): string {
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function attachConditionToCollection(
        AbstractCollection $collection,
        Combine $combine
    ): void {
        $this->_connection = $collection->getResource()->getConnection();
        $this->_joinTablesToCollection($collection, $combine);
        $whereExpression = (string)$this->_getMappedSqlCombination($combine);
        if (!empty($whereExpression)) {
            $collection->getSelect()->where($whereExpression);
            $this->buildConditions($collection, $combine);
        }
    }

    /**
     * Build sql conditions from combination.
     *
     * @param AbstractCollection $collection
     * @param Combine $combine
     * @return void
     */
    private function buildConditions(AbstractCollection $collection, Combine $combine) : void
    {
        if (!empty($combine->getConditions())) {
            $conditions = '';
            $attributeField = '';
            foreach ($combine->getConditions() as $condition) {
                if ($condition->getData('attribute') === \Magento\Catalog\Api\Data\ProductInterface::SKU
                    && $condition->getData('operator') === '()'
                ) {
                    $conditions = $condition->getData('value');
                    $attributeField = $this->_connection->quoteIdentifier($condition->getMappedSqlField());
                }
            }

            if (!empty($conditions) && !empty($attributeField)) {
                $conditions = $this->_connection->quote(
                    array_map('trim', explode(',', $conditions))
                );
                $collection->getSelect()->reset(Select::ORDER);
                $collection->getSelect()->order(
                    $this->_expressionFactory->create(
                        ['expression' => "FIELD($attributeField, $conditions)"]
                    )
                );
            }
        }
    }
}
