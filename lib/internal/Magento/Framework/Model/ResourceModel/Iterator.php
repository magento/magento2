<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Active record implementation
 */
namespace Magento\Framework\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class \Magento\Framework\Model\ResourceModel\Iterator
 *
 * @since 2.0.0
 */
class Iterator extends \Magento\Framework\DataObject
{
    /**
     * Walk over records fetched from query one by one using callback function
     *
     * @param \Zend_Db_Statement_Interface|\Magento\Framework\DB\Select|string $query
     * @param array|string $callbacks
     * @param array $args
     * @param AdapterInterface $connection
     * @return \Magento\Framework\Model\ResourceModel\Iterator
     * @since 2.0.0
     */
    public function walk($query, array $callbacks, array $args = [], $connection = null)
    {
        $stmt = $this->_getStatement($query, $connection);
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
     * @param \Zend_Db_Statement_Interface|\Magento\Framework\DB\Select|string $query
     * @param AdapterInterface $connection
     * @return \Zend_Db_Statement_Interface
     * @throws LocalizedException
     * @since 2.0.0
     */
    protected function _getStatement($query, AdapterInterface $connection = null)
    {
        if ($query instanceof \Zend_Db_Statement_Interface) {
            return $query;
        }

        if ($query instanceof \Zend_Db_Select) {
            return $query->query();
        }

        if (is_string($query)) {
            if (!$connection instanceof AdapterInterface) {
                throw new LocalizedException(new Phrase('Invalid connection'));
            }
            return $connection->query($query);
        }

        throw new LocalizedException(new Phrase('Invalid query'));
    }
}
