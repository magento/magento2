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
 * @category    Magento
 * @package     unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_TestCase_ZendDbAdapterAbstract extends PHPUnit_Framework_TestCase
{
    /**
     * Create an adapter mock object
     *
     * @param string $adapterClass
     * @param array $mockMethods
     * @param array|null $constructArgs
     * @param string $mockStatementMethods
     * @return Zend_Db_Adapter_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAdapterMock($adapterClass, $mockMethods, $constructArgs = array(),
        $mockStatementMethods = 'execute'
    ) {
        if (empty($constructArgs)) {
            $adapter = $this->getMock($adapterClass, $mockMethods, array(), '', false);
        } else {
            $adapter = $this->getMock($adapterClass, $mockMethods, $constructArgs);
        }
        if (null !== $mockStatementMethods) {
            $statement = $this->getMock('Zend_Db_Statement', array_merge((array)$mockStatementMethods,
                    array('closeCursor', 'columnCount', 'errorCode', 'errorInfo', 'fetch', 'nextRowset', 'rowCount')
                ), array(), '', false
            );
            $adapter->expects($this->any())
                ->method('query')
                ->will($this->returnValue($statement));
        }
        return $adapter;
    }
}
