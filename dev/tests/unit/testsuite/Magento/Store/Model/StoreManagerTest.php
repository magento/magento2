<?php
/**
 * Test class for \Magento\Store\Model\StoreManager
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

class StoreManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManager
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
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storage;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock('Magento\Store\Model\StorageFactory', [], [], '', false);
        $this->_requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
        $this->_configMock = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );
        $this->_storage = $this->getMock('Magento\Store\Model\StoreManagerInterface');

        $this->_model = new \Magento\Store\Model\StoreManager(
            $this->_factoryMock,
            $this->_requestMock,
            $this->_configMock,
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
        $this->_storage->expects($this->once())->method($method)->will($this->returnValueMap([$map]));

        $actualResult = call_user_func_array([$this->_model, $method], $arguments);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function proxyMethodDataProvider()
    {
        return [
            'clearWebsiteCache' => ['clearWebsiteCache', ['id' => 101], null],
            'getGroups' => ['getGroups', ['withDefault' => true, 'codeKey' => true], 'groupsArray'],
            'getGroup' => ['getGroup', ['id' => 102], 'groupObject'],
            'getDefaultStoreView' => ['getDefaultStoreView', [], 'defaultStoreObject'],
            'reinitStores' => ['reinitStores', [], null],
            'getWebsites' => ['getWebsites', ['withDefault' => true, 'codeKey' => true], 'websitesArray'],
            'getWebsite' => ['getWebsite', ['id' => 103], 'websiteObject'],
            'getStores' => ['getStores', ['withDefault' => true, 'codeKey' => true], 'storesArray'],
            'getStore' => ['getStore', ['id' => 104], 'storeObject'],
            'hasSingleStore' => ['hasSingleStore', [], 'singleStoreResult'],
        ];
    }

    public function testGetStorageWithCurrentStore()
    {
        $arguments = [
            'isSingleStoreAllowed' => true,
            'currentStore' => 'current_store_code',
            'scopeCode' => 'scope_code',
            'scopeType' => 'scope_type',
        ];

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
        $arguments = [
            'isSingleStoreAllowed' => false,
            'currentStore' => null,
            'scopeCode' => 'scope_code',
            'scopeType' => 'scope_type',
        ];

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
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue(true)
        );

        $this->_storage->expects($this->once())->method('hasSingleStore')->will($this->returnValue(true));
        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $this->assertTrue($this->_model->isSingleStoreMode());
    }

    public function testIsSingleStoreModeWhenSingleStoreModeDisabledAndHasSingleStore()
    {
        $this->_configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue(false)
        );

        $this->_storage->expects($this->once())->method('hasSingleStore')->will($this->returnValue(true));

        $this->_factoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_storage));

        $this->assertFalse($this->_model->isSingleStoreMode());
    }
}
