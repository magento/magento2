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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Db adapter. Reader.
 * Get unique acl resource identifiers from source table
 */
namespace Magento\Tools\Migration\Acl\Db;

class Reader
{
    /**
     * Source table name
     *
     * @var string
     */
    protected $_tableName;

    /**
     * DB adapter
     *
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * @param \Zend_Db_Adapter_Abstract $adapter
     * @param string $tableName source table
     */
    public function __construct(\Zend_Db_Adapter_Abstract $adapter, $tableName)
    {
        $this->_tableName = $tableName;
        $this->_adapter = $adapter;
    }

    /**
     * Get list of unique resource identifiers
     * Format: [resource] => [count items]
     * @return array
     */
    public function fetchAll()
    {
        $select = $this->_adapter->select();
        $select->from(
            $this->_tableName,
            array()
        )->columns(
            array('resource_id' => 'resource_id', 'itemsCount' => new \Zend_Db_Expr('count(*)'))
        )->group(
            'resource_id'
        );
        return $this->_adapter->fetchPairs($select);
    }
}
