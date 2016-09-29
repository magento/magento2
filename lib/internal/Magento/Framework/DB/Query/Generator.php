<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Generate select query list with predefined items count in each select item.
     *
     * @param string $rangeField
     * @param \Magento\Framework\DB\Select $select
     * @param int $batchSize
     * @return BatchIterator
     * @throws LocalizedException
     */
    public function generate($rangeField, \Magento\Framework\DB\Select $select, $batchSize = 100)
    {
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
}
