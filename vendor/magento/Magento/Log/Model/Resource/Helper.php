<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Resource helper for specific requests to MySQL DB
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model\Resource;

class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Returns information about table in DB
     *
     * @param string $table
     * @return array
     */
    public function getTableInfo($table)
    {
        $adapter = $this->_getReadAdapter();
        $tableName = $adapter->getTableName($table);

        $query = $adapter->quoteInto('SHOW TABLE STATUS LIKE ?', $tableName);
        $status = $adapter->fetchRow($query);
        if (!$status) {
            return [];
        }

        return [
            'name' => $tableName,
            'rows' => $status['Rows'],
            'data_length' => $status['Data_length'],
            'index_length' => $status['Index_length']
        ];
    }
}
