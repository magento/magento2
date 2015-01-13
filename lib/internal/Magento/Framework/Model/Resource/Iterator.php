<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Active record implementation
 */
namespace Magento\Framework\Model\Resource;


class Iterator extends \Magento\Framework\Object
{
    /**
     * Walk over records fetched from query one by one using callback function
     *
     * @param \Zend_Db_Statement_Interface|Zend_Db_Select|string $query
     * @param array|string $callbacks
     * @param array $args
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $adapter
     * @return \Magento\Framework\Model\Resource\Iterator
     */
    public function walk($query, array $callbacks, array $args = [], $adapter = null)
    {
        $stmt = $this->_getStatement($query, $adapter);
        $args['idx'] = 0;
        while ($row = $stmt->fetch()) {
            $args['row'] = $row;
            foreach ($callbacks as $callback) {
                $result = call_user_func($callback, $args);
                if (!empty($result)) {
                    $args = array_merge($args, (array)$result);
                }
            }
            $args['idx']++;
        }

        return $this;
    }

    /**
     * Fetch Zend statement instance
     *
     * @param \Zend_Db_Statement_Interface|Zend_Db_Select|string $query
     * @param \Zend_Db_Adapter_Abstract $conn
     * @return \Zend_Db_Statement_Interface
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getStatement($query, $conn = null)
    {
        if ($query instanceof \Zend_Db_Statement_Interface) {
            return $query;
        }

        if ($query instanceof \Zend_Db_Select) {
            return $query->query();
        }

        if (is_string($query)) {
            if (!$conn instanceof \Zend_Db_Adapter_Abstract) {
                throw new \Magento\Framework\Model\Exception(__('Invalid connection'));
            }
            return $conn->query($query);
        }

        throw new \Magento\Framework\Model\Exception(__('Invalid query'));
    }
}
