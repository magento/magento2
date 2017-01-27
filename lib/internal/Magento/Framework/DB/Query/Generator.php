<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\Exception\LocalizedException;

/**
 * Query generator
 */
class Generator
{
    /**
     * @var \Magento\Framework\DB\Query\BatchIteratorFactory
     */
    private $iteratorFactory;

    /**
     * Initialize dependencies.
     *
     * @param BatchIteratorFactory $iteratorFactory
     */
    public function __construct(BatchIteratorFactory $iteratorFactory)
    {
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * Generate select query list with predefined items count in each select item
     *
     * Generates select parameters - batchSize, correlationName, rangeField, rangeFieldAlias, batchStrategy
     * to obtain instance of iterator. The behavior of the iterator will depend on the parameters passed to it.
     * For example: by default for $batchStrategy parameter used
     * \Magento\Framework\DB\Query\BatchIteratorFactory::UNIQUE_FIELD_ITERATOR. This parameter is determine, what
     * instance of Iterator will be returned.
     *
     * Other params:
     * select - represents the select object, that should be passed into Iterator.
     * batchSize - sets the number of items in select.
     * correlationName - is the base table involved in the select.
     * rangeField - this is the basic field which used to split select.
     * rangeFieldAlias - alias of range field.
     *
     * @see BatchIteratorFactory
     * @param string $rangeField -  Field which is used for the range mechanism in select
     * @param \Magento\Framework\DB\Select $select
     * @param int $batchSize - Determines on how many parts will be divided
     * the number of values in the select.
     * @param string $batchStrategy It determines which strategy is chosen
     * @return \Iterator
     * @throws LocalizedException Throws if incorrect "FROM" part in \Select exists
     */
    public function generate(
        $rangeField,
        \Magento\Framework\DB\Select $select,
        $batchSize = 100,
        $batchStrategy = \Magento\Framework\DB\Query\BatchIteratorFactory::UNIQUE_FIELD_ITERATOR
    ) {
        $fromSelect = $select->getPart(\Magento\Framework\DB\Select::FROM);
        if (empty($fromSelect)) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase('Select object must have correct "FROM" part')
            );
        }

        $fieldCorrelationName = '';
        foreach ($fromSelect as $correlationName => $fromPart) {
            if ($fromPart['joinType'] == \Magento\Framework\DB\Select::FROM) {
                $fieldCorrelationName = $correlationName;
                break;
            }
        }

        $columns = $select->getPart(\Magento\Framework\DB\Select::COLUMNS);
        /**
         * Calculate $rangeField alias
         */
        $rangeFieldAlias = $rangeField;
        foreach ($columns as $column) {
            list($table, $columnName, $alias) = $column;
            if ($table == $fieldCorrelationName && $columnName == $rangeField) {
                $rangeFieldAlias = $alias ?: $rangeField;
                break;
            }
        }

        return $this->iteratorFactory->create(
            [
                'select' => $select,
                'batchSize' => $batchSize,
                'correlationName' => $fieldCorrelationName,
                'rangeField' => $rangeField,
                'rangeFieldAlias' => $rangeFieldAlias,
                'batchStrategy' => $batchStrategy
            ]
        );
    }
}
