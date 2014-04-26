<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Active record implementation
 */
namespace Magento\Framework\Model\Resource;

use Magento\Framework\Model\Resource\Zend_Db_Select;

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
    public function walk($query, array $callbacks, array $args = array(), $adapter = null)
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
