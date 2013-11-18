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
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Resource\Category;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DB\Adapter\Pdo\Mysql|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dbAdapterMock;

    protected function setUp()
    {
        $this->_dbAdapterMock = $this->getMock('Magento\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
    }

    /**
     * @param array $methods
     * @return \Magento\Catalog\Model\Resource\Category\Flat|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getModelMock(array $methods = array())
    {
        return $this->getMockBuilder('Magento\Catalog\Model\Resource\Category\Flat')
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreateTableDoesNotInvokeDdlOperationsIfTheyAreNotAllowed()
    {
        $model = $this->_getModelMock(array('_createTable', '_getWriteAdapter'));

        // Pretend that some transaction has been started
        $this->_dbAdapterMock->expects($this->any())->method('getTransactionLevel')->will($this->returnValue(1));
        $model->expects($this->any())->method('_getWriteAdapter')->will($this->returnValue($this->_dbAdapterMock));

        $model->expects($this->never())->method('_createTable');
        $model->createTable(1);
    }

    public function testCreateTableInvokesDdlOperationsIfTheyAreAllowed()
    {
        $model = $model = $this->_getModelMock(array('_createTable', '_getWriteAdapter'));

        // Pretend that no transactions have been started
        $this->_dbAdapterMock->expects($this->any())->method('getTransactionLevel')->will($this->returnValue(0));
        $model->expects($this->any())->method('_getWriteAdapter')->will($this->returnValue($this->_dbAdapterMock));

        $model->expects($this->atLeastOnce())->method('_createTable');
        $model->createTable(1);
    }

    public function testReindexAllCreatesFlatTablesAndInvokesRebuildProcessWithoutArguments()
    {
        $model = $this->_getModelMock(array('_createTables', 'rebuild', 'commit', 'beginTransaction', 'rollBack'));
        $model->expects($this->once())->method('_createTables');
        $model->expects($this->once())->method('rebuild')->with($this->isNull());
        $model->reindexAll();
    }

    public function testRebuildDoesNotInvokeDdlOperationsIfTheyAreNotAllowed()
    {
        $model = $this->_getModelMock(array('_createTable', '_getWriteAdapter', '_populateFlatTables'));

        // Pretend that some transaction has been started
        $this->_dbAdapterMock->expects($this->any())->method('getTransactionLevel')->will($this->returnValue(1));
        $model->expects($this->any())->method('_getWriteAdapter')->will($this->returnValue($this->_dbAdapterMock));

        $model->expects($this->never())->method('_createTable');

        $store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));

        $model->rebuild(array($store));
    }

    public function testRebuildInvokesDdlOperationsIfTheyAreAllowed()
    {
        $model = $this->_getModelMock(array('_createTable', '_getWriteAdapter', '_populateFlatTables'));

        // Pretend that no transactions have been started
        $this->_dbAdapterMock->expects($this->any())->method('getTransactionLevel')->will($this->returnValue(0));
        $model->expects($this->any())->method('_getWriteAdapter')->will($this->returnValue($this->_dbAdapterMock));

        $model->expects($this->atLeastOnce())->method('_createTable');

        $store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));

        $model->rebuild(array($store));
    }
}
