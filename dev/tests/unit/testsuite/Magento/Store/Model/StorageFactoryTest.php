<?php
/**
 * Test class for \Magento\Store\Model\StorageFactory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class StorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StorageFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sidResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \Magento\Framework\Stdlib\Cookie
     */
    protected $_cookie;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var string
     */
    protected $_defaultStorage = 'Magento\Store\Model\Storage\DefaultStorage';

    /**
     * @var string
     */
    protected $_dbStorage = 'Magento\Store\Model\Storage\Db';

    /**
     * @var array
     */
    protected $_arguments = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $website;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $group;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $helper;

    protected function setUp()
    {
        $this->_arguments = ['test' => 'argument', 'scopeCode' => '', 'scopeType' => ''];
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->_sidResolverMock = $this->getMock(
            '\Magento\Framework\Session\SidResolverInterface',
            [],
            [],
            '',
            false
        );
        $this->helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->_httpContext = $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false);
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);

        $this->_model = $this->helper->getObject('Magento\Store\Model\StorageFactory', [
            'objectManager' => $this->_objectManagerMock,
            'eventManager' => $this->_eventManagerMock,
            'sidResolver' => $this->_sidResolverMock,
            'appState' => $this->_appStateMock,
            'httpContext' => $this->_httpContext,
            'scopeConfig' => $this->_scopeConfig,
            'request' => $this->request,
        ]);

        $this->store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->store->expects($this->any())->method('getCode')->will($this->returnValue('store1'));

        $this->website = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $this->website->expects($this->any())->method('getCode')->will($this->returnValue('website1'));

        $this->group = $this->getMock(
            'Magento\Store\Model\Group',
            ['getDefaultStoreId', '__sleep', '__wakeup'],
            [],
            '',
            false
        );

        $this->storage = $this->getMock('Magento\Store\Model\Storage\Db', [], [], '', false);
        $this->storage->expects($this->any())->method('getWebsite')->will($this->returnValue($this->website));
        $this->storage->expects($this->any())->method('getWebsites')->will($this->returnValue(
            ['website1' => $this->website]
        ));
        $this->storage->expects($this->any())->method('getGroups')->will($this->returnValue([11 => $this->group]));
        $this->storage->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->storage->expects($this->any())
            ->method('getStores')
            ->will($this->returnCallback(function ($withDefault, $codeKey) {
                if ($codeKey) {
                    return ['store1' => $this->store];
                } else {
                    return [21 => $this->store];
                }
            }));
    }

    public function testGetModeWithInternalCache()
    {
        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->_storeManager->expects($this->exactly(2))->method('getStore')->will($this->returnValue($store));

        $this->_scopeConfig->expects(
            $this->at(0)
        )->method(
            'isSetFlag'
        )->with(
            \Magento\Framework\Session\SidResolver::XML_PATH_USE_FRONTEND_SID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->will(
            $this->returnValue(true)
        );

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->_dbStorage
        )->will(
            $this->returnValue($this->_storeManager)
        );

        $this->_eventManagerMock->expects(
            $this->once()
        )->method(
            'dispatch'
        )->with(
            'core_app_init_current_store_after'
        );


        $this->_sidResolverMock->expects($this->once())->method('setUseSessionInUrl')->with(true);

        /** test create instance */
        $this->assertEquals($this->_storeManager, $this->_model->get($this->_arguments));

        /** test read instance from internal cache */
        $this->assertEquals($this->_storeManager, $this->_model->get($this->_arguments));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetWithInvalidStorageClassName()
    {
        $invalidObject = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->_dbStorage
        )->will(
            $this->returnValue($invalidObject)
        );

        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_sidResolverMock->expects($this->never())->method('setUseSessionInUrl');

        /** test create instance */
        $this->assertEquals($this->_storeManager, $this->_model->get($this->_arguments));

        /** test read instance from internal cache */
        $this->assertEquals($this->_storeManager, $this->_model->get($this->_arguments));
    }

    /**
     * @covers \Magento\Store\Model\StorageFactory::_reinitStores
     * @covers \Magento\Store\Model\StorageFactory::_getStoreByGroup
     * @covers \Magento\Store\Model\StorageFactory::_getStoreByWebsite
     * @covers \Magento\Store\Model\StorageFactory::_checkCookieStore
     * @covers \Magento\Store\Model\StorageFactory::_checkRequestStore
     *
     * @dataProvider getWithStoresReinitDataProvider
     *
     * @param string $scopeCode
     * @param string $scopeType
     * @param int $defaultStoreId
     * @param int $defaultGroupId
     * @param string $expectedStore
     */
    public function testGetWithStoresReinit($scopeCode, $scopeType, $defaultStoreId, $defaultGroupId, $expectedStore)
    {
        $this->_arguments['scopeCode'] = $scopeCode;
        $this->_arguments['scopeType'] = $scopeType;

        $this->website->expects($defaultGroupId === null ? $this->never() : $this->atLeastOnce())
            ->method('getDefaultGroupId')
            ->will($this->returnValue($defaultGroupId));

        $this->group->expects($this->any())->method('getDefaultStoreId')->will($this->returnValue($defaultStoreId));

        $this->storage->expects($this->any())->method('setCurrentStore')->with($expectedStore);

        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($this->storage));

        $this->assertEquals($this->storage, $this->_model->get($this->_arguments));
    }

    /**
     * @return array
     */
    public function getWithStoresReinitDataProvider()
    {
        return [
            ['', '', 21, 11, 'store1'],
            ['11', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP, 21, null, 'store1'],
            ['12', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP, 22, null, null],
            ['11', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP, null, null, null],
            ['website1', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, 21, 11, 'store1'],
            ['31', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, 22, null, null],
            ['website1', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, null, 0, null],
        ];
    }

    /**
     * @expectedException \Magento\Framework\App\InitException
     */
    public function testGetWithStoresReinitUnknownScopeType()
    {
        $this->_arguments['scopeCode'] = 'unknown';
        $this->_arguments['scopeType'] = 'unknown';

        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($this->storage));

        $this->_model->get($this->_arguments);
    }

    /**
     * @covers \Magento\Store\Model\StorageFactory::_checkCookieStore
     * @covers \Magento\Store\Model\StorageFactory::getActiveStoreByCode
     * @covers \Magento\Store\Model\StorageFactory::setCurrentStore
     *
     * @dataProvider getFromCookieDataProvider
     */
    public function testGetFromCookie($scopeCode, $scopeType)
    {
        $this->_arguments['scopeCode'] = $scopeCode;
        $this->_arguments['scopeType'] = $scopeType;

        $this->website->expects($this->any())->method('getDefaultGroupId')->will($this->returnValue(11));

        $this->group->expects($this->any())->method('getDefaultStoreId')->will($this->returnValue(21));

        $this->store->expects($this->once())->method('getId')->will($this->returnValue(21));
        $this->store->expects($this->once())->method('getIsActive')->will($this->returnValue(true));
        $this->store->expects($this->once())->method('getStoreCodeFromCookie')->will($this->returnValue('store1'));

        $this->storage->expects($this->any())->method('setCurrentStore')->with('store1');

        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($this->storage));

        $this->assertEquals($this->storage, $this->_model->get($this->_arguments));
    }

    /**
     * @return array
     */
    public function getFromCookieDataProvider()
    {
        return [
            ['website1', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE],
            ['11', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP],
            ['store1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE],
        ];
    }

    /**
     * @covers \Magento\Store\Model\StorageFactory::_checkRequestStore
     * @covers \Magento\Store\Model\StorageFactory::getActiveStoreByCode
     * @covers \Magento\Store\Model\StorageFactory::setCurrentStore
     *
     * @dataProvider getFromRequestDataProvider
     *
     * @param bool $isActiveStore
     * @param bool $isDefault
     * @param string $cookieCall
     */
    public function testGetFromRequest($isActiveStore, $isDefault, $cookieCall = '')
    {
        $storeDefault = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        if (!$isDefault) {
            $storeDefault->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(22));
            $this->_httpContext->expects($this->once())->method('setValue')->with(
                \Magento\Store\Model\Store::ENTITY,
                'store1',
                \Magento\Store\Model\Store::DEFAULT_CODE
            )->will($this->returnSelf());
        }

        $this->website->expects($this->any())->method('getDefaultStore')->will(
            $this->returnValue(!$isDefault ? $storeDefault : $this->store)
        );
        $this->website->expects($this->atLeastOnce())->method('getDefaultGroupId')->will($this->returnValue(11));

        $this->group->expects($this->any())->method('getDefaultStoreId')->will($this->returnValue(21));

        $this->store->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(21));
        $this->store->expects($this->once())->method('getIsActive')->will($this->returnValue($isActiveStore));
        $this->store->expects($this->any())->method('getWebsite')->will($this->returnValue($this->website));
        if (!empty($cookieCall)) {
            $this->store->expects($this->once())->method($cookieCall);
        }
        $this->storage->expects($this->any())->method('setCurrentStore')->with('store1');

        $numCreateCookieCalls = $isDefault ? 0 : 1;
        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($this->storage));

        $this->request->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('___store')
            ->will($this->returnValue('store1'));

        $this->assertEquals($this->storage, $this->_model->get($this->_arguments));
    }

    /**
     * @return array
     */
    public function getFromRequestDataProvider()
    {
        return [
            [false, true],
            [true, true, 'deleteCookie'],
            [true, false, 'setCookie'],
        ];
    }
}
