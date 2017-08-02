<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\Exception\LocalizedException;

/**
 * Query generator
 * @since 2.1.3
 */
class Generator
{
    /**
     * @var \Magento\Framework\DB\Query\BatchIteratorFactory
     * @since 2.1.3
     */
    private $iteratorFactory;

    /**
     * @var \Magento\Framework\DB\Query\BatchRangeIteratorFactory
     * @since 2.2.0
     */
    private $rangeIteratorFactory;

    /**
     * Initialize dependencies.
     *
     * @param BatchIteratorFactory $iteratorFactory
     * @param BatchRangeIteratorFactory $rangeIteratorFactory
     * @since 2.1.3
     */
    public function __construct(
        BatchIteratorFactory $iteratorFactory,
        BatchRangeIteratorFactory $rangeIteratorFactory = null
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->rangeIteratorFactory = $rangeIteratorFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\DB\Query\BatchRangeIteratorFactory::class);
    }

    /**
     * Generate select query list with predefined items count in each select item
     *
     * Generates select parameters - batchSize, correlationName, rangeField, rangeFieldAlias
     * to obtain instance of iterator. The behavior of the iterator will depend on the parameters passed to it.
     * For example: by default for $batchStrategy parameter used
     * \Magento\Framework\DB\Query\BatchIteratorInterface::UNIQUE_FIELD_ITERATOR. This parameter is determine, what
     * instance of Iterator will be returned.
     *
     * Other params:
     * select - represents the select object, that should be passed into Iterator.
     * batchSize - sets the number of items in select.
     * correlationName - is the base table involved in the select.
     * rangeField - this is the basic field which used to split select.
     * rangeFieldAlias - alias of range field.
     *
     * @see \Magento\Framework\DB\Query\BatchIteratorInterface
     * @param string $rangeField -  Field which is used for the range mechanism in select
     * @param \Magento\Framework\DB\Select $select
     * @param int $batchSize - Determines on how many parts will be divided
     * the number of values in the select.
     * @param string $batchStrategy It determines which strategy is chosen
     * @return BatchIteratorInterface
     * @throws LocalizedException Throws if incorrect "FROM" part in \Select exists
     * @since 2.1.3
     */
    public function generate(
        $rangeField,
        \Magento\Framework\DB\Select $select,
        $batchSize = 100,
        $batchStrategy = \Magento\Framework\DB\Query\BatchIteratorInterface::UNIQUE_FIELD_ITERATOR
    ) {
        if ($batchStrategy == \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR) {
            return $this->generateByRange($rangeField, $select, $batchSize);
        }

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
                'rangeFieldAlias' => $rangeFieldAlias
            ]
        );
    }

    /**
     * Generate select query list with predefined items count in each select item.
     *
     * Generates select parameters - batchSize, correlationName, rangeField, rangeFieldAlias
     * to obtain instance of BatchRangeIterator.
     *
     * Other params:
     * select - represents the select object, that should be passed into Iterator.
     * batchSize - sets the number of items in select.
     * correlationName - is the base table involved in the select.
     * rangeField - this is the basic field which used to split select.
     * rangeFieldAlias - alias of range field.
     *
     * @see BatchRangeIterator
     * @param string $rangeField -  Field which is used for the range mechanism in select
     * @param \Magento\Framework\DB\Select $select
     * @param int $batchSize
     * @return BatchIteratorInterface
     * @throws LocalizedException Throws if incorrect "FROM" part in \Select exists
     * @see \Magento\Framework\DB\Query\Generator
     * @deprecated 2.2.0 This is a temporary solution which is made due to the fact that we
     *             can't change method generate() in version 2.1 due to a backwards incompatibility.
     *             In 2.2 version need to use original method generate() with additional parameter.
     * @since 2.2.0
     */
    public function generateByRange(
        $rangeField,
        \Magento\Framework\DB\Select $select,
        $batchSize = 100
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

        return $this->rangeIteratorFactory->create(
            [
                'select' => $select,
                'batchSize' => $batchSize,
                'correlationName' => $fieldCorrelationName,
                'rangeField' => $rangeField,
                'rangeFieldAlias' => $rangeFieldAlias,
            ]
        );
    }
}
