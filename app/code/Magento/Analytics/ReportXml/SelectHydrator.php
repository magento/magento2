<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;

/**
 * Class SelectHydrator
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
     * SelectHydrator constructor.
     * @param ResourceConnection $resourceConnection
     * @param array $selectParts
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        $selectParts = []
    ) {
        $this->resourceConnection = $resourceConnection;
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
        foreach ($selectParts as $partName => $partValue) {
            $select->setPart($partName, $partValue);
        }
        return $select;
    }
}
