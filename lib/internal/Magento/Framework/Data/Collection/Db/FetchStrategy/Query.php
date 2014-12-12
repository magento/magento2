<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Retrieving collection data by querying a database
 */
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

class Query implements \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetchAll(\Zend_Db_Select $select, array $bindParams = [])
    {
        return $select->getAdapter()->fetchAll($select, $bindParams);
    }
}
