<?php
/**
 * Test class for \Magento\Store\Model\StorageFactory
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_arguments = array();

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


    protected function setUp()
    {
        $this->_arguments = array('test' => 'argument', 'scopeCode' => '', 'scopeType' => '');
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManager');
        $this->_eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->_logMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->_sidResolverMock = $this->getMock(
            '\Magento\Framework\Session\SidResolverInterface',
            array(),
            array(),
            '',
            false
        );
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->_cookie = $this->getMock('Magento\Framework\Stdlib\Cookie', array(), array(), '', false);
        $this->_httpContext = $this->getMock('Magento\Framework\App\Http\Context', array(), array(), '', false);
        $this->_scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', array(), array(), '', false);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject('\Magento\Store\Model\StorageFactory', array(
            'objectManager' => $this->_objectManagerMock,
            'eventManager' => $this->_eventManagerMock,
            'logger' => $this->_logMock,
            'sidResolver' => $this->_sidResolverMock,
            'appState' => $this->_appStateMock,
            'cookie' => $this->_cookie,
            'httpContext' => $this->_httpContext,
            'scopeConfig' => $this->_scopeConfig,
            'request' => $this->request,
            'defaultStorageClassName' => $this->_defaultStorage,
            'installedStorageClassName' => $this->_dbStorage
        ));

        $this->store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->store->expects($this->any())->method('getCode')->will($this->returnValue('store1'));

        $this->website = $this->getMock('Magento\Store\Model\Website', array(), array(), '', false);
        $this->website->expects($this->any())->method('getCode')->will($this->returnValue('website1'));

        $this->group = $this->getMock(
            'Magento\Store\Model\Group',
            array('getDefaultStoreId', '__sleep', '__wakeup'),
            array(),
            '',
            false
        );

        $this->storage = $this->getMock('Magento\Store\Model\Storage\Db', array(), array(), '', false);
        $this->storage->expects($this->any())->method('getWebsite')->will($this->returnValue($this->website));
        $this->storage->expects($this->any())->method('getWebsites')->will($this->returnValue(
            array('website1' => $this->website)
        ));
        $this->storage->expects($this->any())->method('getGroups')->will($this->returnValue(array(11 => $this->group)));
        $this->storage->expects($this->any())->method('getStore')->will($this->returnValue($this->store));

        $this->storage->expects($this->any())
            ->method('getStores')
            ->will($this->returnCallback(function ($withDefault, $codeKey) {
                if ($codeKey) {
                    return array('store1' => $this->store);
                } else {
                    return array(21 => $this->store);
                }
            }));
    }

    public function testGetInNotInstalledModeWithInternalCache()
    {
        $this->_appStateMock->expects($this->exactly(2))->method('isInstalled')->will($this->returnValue(false));

        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->_defaultStorage
        )->will(
            $this->returnValue($this->_storeManager)
        );

        $this->_eventManagerMock->expects($this->never())->method('dispatch');
        $this->_logMock->expects($this->never())->method('initForStore');
        $this->_sidResolverMock->expects($this->never())->method('setUseSessionInUrl');

        /** test create instance */
        $this->assertEquals($this->_storeManager, $this->_model->get($this->_arguments));

        /** test read instance from internal cache */
        $this->assertEquals($this->_storeManager, $this->_model->get($this->_arguments));
    }

    public function testGetInstalledModeWithInternalCache()
    {
        $this->_appStateMock->expects($this->exactly(2))->method('isInstalled')->will($this->returnValue(true));

        $store = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);

        $this->_storeManager->expects($this->exactly(3))->method('getStore')->will($this->returnValue($store));

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

        $this->_scopeConfig->expects(
            $this->at(1)
        )->method(
            'isSetFlag'
        )->with(
            'dev/log/active',
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

        $this->_logMock->expects($this->once())->method('unsetLoggers');
        $this->_logMock->expects($this->exactly(2))->method('addStreamLog');

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
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

        $invalidObject = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);

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
        $this->_logMock->expects($this->never())->method('initForStore');
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
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

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
        return array(
            array('', '', 21, 11, 'store1'),
            array('11', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP, 21, null, 'store1'),
            array('12', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP, 22, null, null),
            array('11', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP, null, null, null),
            array('website1', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, 21, 11, 'store1'),
            array('31', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, 22, null, null),
            array('website1', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, null, 0, null),
        );
    }

    /**
     * @expectedException \Magento\Store\Model\Exception
     */
    public function testGetWithStoresReinitUnknownScopeType()
    {
        $this->_arguments['scopeCode'] = 'unknown';
        $this->_arguments['scopeType'] = 'unknown';

        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($this->storage));
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

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

        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

        $this->website->expects($this->any())->method('getDefaultGroupId')->will($this->returnValue(11));

        $this->group->expects($this->any())->method('getDefaultStoreId')->will($this->returnValue(21));

        $this->store->expects($this->once())->method('getId')->will($this->returnValue(21));
        $this->store->expects($this->once())->method('getIsActive')->will($this->returnValue(true));

        $this->storage->expects($this->any())->method('setCurrentStore')->with('store1');

        $this->_objectManagerMock->expects($this->once())->method('create')->will($this->returnValue($this->storage));

        $this->_cookie->expects($this->atLeastOnce())->method('get')->will($this->returnValue('store1'));

        $this->assertEquals($this->storage, $this->_model->get($this->_arguments));
    }

    /**
     * @return array
     */
    public function getFromCookieDataProvider()
    {
        return array(
            array('website1', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE),
            array('11', \Magento\Store\Model\ScopeInterface::SCOPE_GROUP),
            array('store1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        );
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
     */
    public function testGetFromRequest($isActiveStore, $isDefault)
    {
        $this->_appStateMock->expects($this->once())->method('isInstalled')->will($this->returnValue(true));

        $storeDefault = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
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

        $this->storage->expects($this->any())->method('setCurrentStore')->with('store1');

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
        return array(
            array(false, true),
            array(true, true),
            array(true, false),
        );
    }
}
