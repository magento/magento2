<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
