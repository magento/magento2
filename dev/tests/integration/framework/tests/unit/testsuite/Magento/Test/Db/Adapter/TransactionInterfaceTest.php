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
 * @package     Magento_Test
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test DB "transparent transaction" features in DB adapter substitutes of integration tests
 *
 * Test behavior of all methods assumed by this interface
 * Due to current architecture of DB adapters, they are copy-pasted.
 * So we need to make sure all these classes have exactly the same behavior.
 */
class Magento_Test_Db_Adapter_TransactionInterfaceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $class
     * @dataProvider transparentTransactionDataProvider
     */
    public function testBeginTransparentTransaction($class)
    {
        $adapter = $this->_getAdapterMock($class);
        $uniqid = uniqid();
        $adapter->expects($this->once())->method('beginTransaction')->will($this->returnValue($uniqid));
        $this->assertSame(0, $adapter->getTransactionLevel());
        $this->assertEquals($uniqid, $adapter->beginTransparentTransaction());
        $this->assertSame(-1, $adapter->getTransactionLevel());
    }

    /**
     * @param string $class
     * @dataProvider transparentTransactionDataProvider
     */
    public function testRollbackTransparentTransaction($class)
    {
        $adapter = $this->_getAdapterMock($class);
        $uniqid = uniqid();
        $adapter->expects($this->once())->method('rollback')->will($this->returnValue($uniqid));
        $adapter->beginTransparentTransaction();
        $this->assertEquals($uniqid, $adapter->rollbackTransparentTransaction());
        $this->assertSame(0, $adapter->getTransactionLevel());
    }

    /**
     * @param string $class
     * @dataProvider transparentTransactionDataProvider
     */
    public function testCommitTransparentTransaction($class)
    {
        $adapter = $this->_getAdapterMock($class);
        $uniqid = uniqid();
        $adapter->expects($this->once())->method('commit')->will($this->returnValue($uniqid));
        $adapter->beginTransparentTransaction();
        $this->assertEquals($uniqid, $adapter->commitTransparentTransaction());
        $this->assertSame(0, $adapter->getTransactionLevel());
    }

    /**
     * @return array
     */
    public function transparentTransactionDataProvider()
    {
        $result = array();
        foreach (glob(realpath(__DIR__ . '/../../../../../../../Magento/Test/Db/Adapter') . '/*.php') as $file) {
            $suffix = basename($file, '.php');
            if (false === strpos($suffix, 'Interface')) {
                $result[] = array("Magento_Test_Db_Adapter_{$suffix}");
            }
        }
        return $result;
    }

    /**
     * Instantiate specified adapter class and block all methods that would try to execute real queries
     *
     * @param string $class
     * @return Magento_Test_Db_Adapter_TransactionInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getAdapterMock($class)
    {
        $adapter = $this->getMock($class, array('beginTransaction', 'rollback', 'commit'), array(), '', false);
        $this->assertInstanceOf('Magento_Test_Db_Adapter_TransactionInterface', $adapter);
        return $adapter;
    }
}
