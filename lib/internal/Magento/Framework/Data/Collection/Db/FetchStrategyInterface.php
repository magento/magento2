<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface of collection data retrieval
 */
namespace Magento\Framework\Data\Collection\Db;

interface FetchStrategyInterface
{
    /**
     * Retrieve all records
     *
     * @param \Zend_Db_Select $select
     * @param array $bindParams
     * @return array
     */
    public function fetchAll(\Zend_Db_Select $select, array $bindParams = []);
}
