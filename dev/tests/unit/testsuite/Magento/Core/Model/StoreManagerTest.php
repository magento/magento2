<?php
/**
 * Test class for \Magento\Core\Model\StoreManager
 *
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
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model;

class StoreManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storage;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock('Magento\Core\Model\Store\StorageFactory', array(), array(), '', false);
        $this->_requestMock = $this->getMock('Magento\App\RequestInterface', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_storage = $this->getMock('Magento\Core\Model\Store\StorageInterface');

        $this->_model = new \Magento\Core\Model\StoreManager(
            $this->_factoryMock,
            $this->_requestMock,
            $this->_helperMock,
            'scope_code',
            'scope_type'
        );
    }

    /**
     * @param $method
     * @param $arguments
     * @param $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethods($method, $arguments, $expectedResult)
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $map = array_values($arguments);
        $map[] = $expectedResult;
        $this->_storage->expects($this->once())->method($method)->will($this->returnValueMap(array($map)));

        $actualResult = call_user_func_array(array($this->_model, $method), $arguments);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function proxyMethodDataProvider()
    {
        return array(
            'getCurrentStore' => array('getCurrentStore', array(), 'currentStoreObject'),
            'getAnyStoreView' => array('getAnyStoreView', array(), 'anyStoreObject'),
            'clearWebsiteCache' => array('clearWebsiteCache', array('id' => 101), null),
            'getGroups' => array('getGroups', array('withDefault' => true, 'codeKey' => true), 'groupsArray'),
            'getGroup' => array('getGroup', array('id' => 102), 'groupObject'),
            'getDefaultStoreView' => array('getDefaultStoreView', array(), 'defaultStoreObject'),
            'reinitStores' => array('reinitStores', array(), null),
            'getWebsites' => array('getWebsites', array('withDefault' => true, 'codeKey' => true), 'websitesArray'),
            'getWebsite' => array('getWebsite', array('id' => 103), 'websiteObject'),
            'getStores' => array('getStores', array('withDefault' => true, 'codeKey' => true), 'storesArray'),
            'getStore' => array('getStore', array('id' => 104), 'storeObject'),
            'hasSingleStore' => array('hasSingleStore', array(), 'singleStoreResult'),
            'throwStoreException' => array('throwStoreException', array(), null)
        );
    }

    public function testGetStorageWithCurrentStore()
    {
        $arguments = array(
            'isSingleStoreAllowed' => true,
            'currentStore' => 'current_store_code',
            'scopeCode' => 'scope_code',
            'scopeType' => 'scope_type'
        );

        $this->_factoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $arguments
        )->will(
            $this->returnValue($this->_storage)
        );

        $this->_storage->expects($this->once())->method('setCurrentStore')->with('current_store_code');

        $this->_model->setCurrentStore('current_store_code');
    }

    public function testGetStorageWithSingleStoreMode()
    {
        $arguments = array(
            'isSingleStoreAllowed' => false,
            'currentStore' => null,
            'scopeCode' => 'scope_code',
            'scopeType' => 'scope_type'
        );

        $this->_factoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $arguments
        )->will(
            $this->returnValue($this->_storage)
        );

        $this->_storage->expects($this->once())->method('setIsSingleStoreModeAllowed')->with(false);

        $this->_model->setIsSingleStoreModeAllowed(false);
    }

    public function testIsSingleStoreModeWhenSingleStoreModeEnabledAndHasSingleStore()
    {
        $this->_helperMock->expects($this->once())->method('isSingleStoreModeEnabled')->will($this->returnValue(true));

        $this->_storage->expects($this->once())->method('hasSingleStore')->will($this->returnValue(true));

        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $this->assertTrue($this->_model->isSingleStoreMode());
    }

    public function testIsSingleStoreModeWhenSingleStoreModeDisabledAndHasSingleStore()
    {
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isSingleStoreModeEnabled'
        )->will(
            $this->returnValue(false)
        );

        $this->_storage->expects($this->once())->method('hasSingleStore')->will($this->returnValue(true));

        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $this->assertFalse($this->_model->isSingleStoreMode());
    }

    public function testGetSafeStoreWithoutException()
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));
        $this->_storage->expects($this->once())->method('getStore')->with(10)->will($this->returnValue('storeObject'));
        $this->_requestMock->expects($this->never())->method('setActionName');
        $this->_model->getSafeStore(10);
    }

    public function testGetSafeStoreWithExceptionWithCurrentStore()
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));
        $this->_storage->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            10
        )->will(
            $this->throwException(new \Exception('test'))
        );

        $this->_storage->expects($this->once())->method('getCurrentStore')->will($this->returnValue('current'));
        $this->_requestMock->expects($this->once())->method('setActionName')->with('noroute');

        $this->assertInstanceOf('Magento\Object', $this->_model->getSafeStore(10));
    }

    /**
     * @expectedException \Magento\Model\Exception
     */
    public function testGetSafeStoreWithExceptionAndWithoutCurrentStore()
    {
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));
        $this->_storage->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            10
        )->will(
            $this->throwException(new \Exception('test'))
        );

        $this->_storage->expects($this->once())->method('getCurrentStore')->will($this->returnValue(false));
        $this->_requestMock->expects($this->never())->method('setActionName');

        $this->_model->getSafeStore(10);
    }
}
