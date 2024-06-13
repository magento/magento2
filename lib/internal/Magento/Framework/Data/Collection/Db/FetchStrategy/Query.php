<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Retrieving collection data by querying a database
 */
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

use Magento\Framework\DB\Select;

class Query implements \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetchAll(Select $select, array $bindParams = [])
    {
        return $select->getConnection()->fetchAll($select, $bindParams);
    }
}
