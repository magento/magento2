<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;

/**
 * Hydrator for report select parts
 */
class SelectHydrator
{
    /**
     * Array of supported Select parts
     *
     * @var array
     */
    private $predefinedSelectParts =
        [
            Select::DISTINCT,
            Select::COLUMNS,
            Select::UNION,
            Select::FROM,
            Select::WHERE,
            Select::GROUP,
            Select::HAVING,
            Select::ORDER,
            Select::LIMIT_COUNT,
            Select::LIMIT_OFFSET,
            Select::FOR_UPDATE
        ];

    /**
     * @var array
     */
    private $selectParts;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ObjectManagerInterface $objectManager
     * @param array $selectParts
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager,
        $selectParts = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->objectManager = $objectManager;
        $this->selectParts = $selectParts;
    }

    /**
     * @return array
     */
    private function getSelectParts()
    {
        return array_merge($this->predefinedSelectParts, $this->selectParts);
    }

    /**
     * Extracts Select metadata parts
     *
     * @param Select $select
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    public function extract(Select $select)
    {
        $parts = [];
        foreach ($this->getSelectParts() as $partName) {
            $parts[$partName] = $select->getPart($partName);
        }
        return $parts;
    }

    /**
     * @param array $selectParts
     * @return Select
     */
    public function recreate(array $selectParts)
    {
        $select = $this->resourceConnection->getConnection()->select();

        $select = $this->processColumns($select, $selectParts);

        foreach ($selectParts as $partName => $partValue) {
            $select->setPart($partName, $partValue);
        }

        return $select;
    }

    /**
     * Process COLUMNS part values and add this part into select.
     *
     * If each column contains information about select expression
     * an object with the type of this expression going to be created and assigned to this column.
     *
     * @param Select $select
     * @param array $selectParts
     * @return Select
     */
    private function processColumns(Select $select, array &$selectParts)
    {
        if (!empty($selectParts[Select::COLUMNS]) && is_array($selectParts[Select::COLUMNS])) {
            $part = [];

            foreach ($selectParts[Select::COLUMNS] as $columnEntry) {
                list($correlationName, $column, $alias) = $columnEntry;
                if (is_array($column) && !empty($column['class'])) {
                    $expression = $this->objectManager->create(
                        $column['class'],
                        isset($column['arguments']) ? $column['arguments'] : []
                    );
                    $part[] = [$correlationName, $expression, $alias];
                } else {
                    $part[] = $columnEntry;
                }
            }

            $select->setPart(Select::COLUMNS, $part);
            unset($selectParts[Select::COLUMNS]);
        }

        return $select;
    }
}
