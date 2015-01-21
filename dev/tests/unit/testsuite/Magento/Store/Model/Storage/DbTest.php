<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Storage;

/**
 * Test class for \Magento\Store\Model\Storage\DefaultStorage
 */
class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Db
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupMock;

    /**
     * @var \Magento\Store\Model\Resource\Website\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_websiteCollectionFactoryMock;

    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeCollectionFactoryMock;

    /**
     * @var \Magento\Store\Model\Resource\Group\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupCollectionFactoryMock;

    protected function setUp()
    {
        $this->_storeFactoryMock = $this->getClassMock('Magento\Store\Model\StoreFactory', ['create']);
        $this->_websiteFactoryMock = $this->getClassMock('Magento\Store\Model\WebsiteFactory', ['create']);
        $this->_groupFactoryMock = $this->getClassMock('Magento\Store\Model\GroupFactory', ['create']);
        $this->_scopeConfig = $this->getClassMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_websiteCollectionFactoryMock = $this->getClassMock(
            'Magento\Store\Model\Resource\Website\CollectionFactory', ['create']
        );
        $this->_groupCollectionFactoryMock = $this->getClassMock(
            'Magento\Store\Model\Resource\Group\CollectionFactory', ['create']
        );
        $this->_storeCollectionFactoryMock = $this->getClassMock(
            'Magento\Store\Model\Resource\Store\CollectionFactory', ['create']
        );
        $this->_appStateMock = $this->getClassMock('Magento\Framework\App\State');
        $this->_groupMock = $this->getClassMock('Magento\Store\Model\Group');
        $this->_websiteMock = $this->getClassMock('Magento\Store\Model\Website');
        $this->_storeMock = $this->getClassMock('Magento\Store\Model\Store');

        $this->_model = new Db(
            $this->_storeFactoryMock,
            $this->_websiteFactoryMock,
            $this->_groupFactoryMock,
            $this->_scopeConfig,
            $this->_websiteCollectionFactoryMock,
            $this->_groupCollectionFactoryMock,
            $this->_storeCollectionFactoryMock,
            $this->_appStateMock,
            true
        );
    }

    protected function getClassMock($className, $methods = [])
    {
        return $this->getMock($className, $methods, [], '', false, false);
    }

    public function testGetWebsite()
    {
        $this->assertSame($this->_websiteMock, $this->_model->getWebsite($this->_websiteMock));

        $this->_websiteFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->_websiteMock));
        $this->_websiteMock->expects($this->at(0))->method('load');
        $this->_websiteMock->expects($this->at(1))->method('__call')->will($this->returnValue(true));
        $this->_websiteMock->expects($this->at(2))->method('__call')->will($this->returnValue('website_id'));
        $this->_websiteMock->expects($this->at(3))->method('getCode')->will($this->returnValue('website_code'));
        $this->assertSame($this->_websiteMock, $this->_model->getWebsite('website_id'));
    }

    /**
     * @expectedException \Magento\Framework\App\InitException
     */
    public function testGetWebsiteInvalidId()
    {
        $this->_websiteFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->_websiteMock));
        $this->_websiteMock->expects($this->at(0))->method('load');
        $this->_websiteMock->expects($this->at(1))->method('__call')->will($this->returnValue(false));
        $this->_model->getWebsite('website_id');
    }

    public function testGetWebsites()
    {
        $expected = [1 => $this->_websiteMock];
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertSame($expected, $this->_model->getWebsites());

        $expected = ['website_code' => $this->_websiteMock];
        $this->assertSame($expected, $this->_model->getWebsites(false, true));
    }

    public function testGetGroup()
    {
        $this->assertSame($this->_groupMock, $this->_model->getGroup($this->_groupMock));

        $groupId = 1;
        $this->_groupFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_groupMock));

        $this->_groupMock->expects($this->at(0))->method('load')->with($groupId);
        $this->_groupMock->expects($this->at(1))->method('__call')->will($this->returnValue(true));
        $this->_groupMock->expects($this->at(2))->method('__call')->will($this->returnValue($groupId));
        $this->assertSame($this->_groupMock, $this->_model->getGroup($groupId));

        $groupId = 'group_id';
        $this->_groupMock->expects($this->never())->method('load');
        $this->_groupMock->expects($this->at(0))->method('__call')->will($this->returnValue($groupId));
        $this->assertSame($this->_groupMock, $this->_model->getGroup($groupId));
    }

    /**
     * @expectedException \Magento\Framework\App\InitException
     */
    public function testGetGroupInvalidId()
    {
        $groupId = 1;
        $this->_groupFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->_groupMock));
        $this->_groupMock->expects($this->at(0))->method('load')->with($groupId);
        $this->_groupMock->expects($this->at(1))->method('__call')->will($this->returnValue(false));
        $this->_model->getGroup($groupId);
    }

    public function testGetGroups()
    {
        $expected = [1 => $this->_groupMock];
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertSame($expected, $this->_model->getGroups());
    }

    protected function prepareMockForReinit()
    {
        $websiteId = 1;
        $websiteCode = 'website_code';
        $groupId = 1;
        $storeId = 1;
        $storeCode = 'store_code';
        $websiteCollection =
            $this->getMock('\Magento\Store\Model\Resource\Website\Collection', [], [], '', false, false);
        $websiteCollection->expects($this->any())->method('setLoadDefault')->with(true);
        $this->mockIterator($websiteCollection, [$this->_websiteMock]);
        $this->_websiteFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_websiteMock));
        $this->_websiteMock
            ->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($websiteCollection));

        $groupCollection =
            $this->getMock('\Magento\Store\Model\Resource\Group\Collection', [], [], '', false, false);
        $groupCollection->expects($this->any())->method('setLoadDefault')->with(true);
        $this->mockIterator($groupCollection, [$this->_groupMock]);
        $this->_groupFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_groupMock));
        $this->_groupMock
            ->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($groupCollection));

        $storeCollection =
            $this->getMock('\Magento\Store\Model\Resource\Store\Collection', [], [], '', false, false);
        $storeCollection->expects($this->any())->method('setLoadDefault')->with(true);
        $this->mockIterator($storeCollection, [$this->_storeMock]);
        $storeCollection
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->_storeMock])));
        $storeCollection
            ->expects($this->any())
            ->method('getLastItem')
            ->will($this->returnValue($this->_storeMock));

        $this->_storeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_storeMock));
        $this->_storeMock
            ->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($storeCollection));

        $this->_websiteCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($websiteCollection));

        $this->_groupCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($groupCollection));

        $this->_storeCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($storeCollection));

        $this->_storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->_storeMock->expects($this->any())->method('getGroupId')->will($this->returnValue($groupId));
        $this->_storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->_storeMock->expects($this->any())->method('getCode')->will($this->returnValue($storeCode));
        $websiteCollection->expects($this->any())->method('getItemById')->will($this->returnValue($this->_websiteMock));
        $groupCollection->expects($this->any())->method('getItemById')->will($this->returnValue($this->_groupMock));

        $this->_groupMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $this->_groupMock->expects($this->any())->method('getId')->will($this->returnValue($groupId));

        $this->_websiteMock->expects($this->any())->method('getId')->will($this->returnValue($websiteId));
        $this->_websiteMock->expects($this->any())->method('getCode')->will($this->returnValue($websiteCode));
        $this->_websiteMock->expects($this->at(3))->method('__call')->will($this->returnValue(true));
    }

    public function testGetStoreGetDefaulStore()
    {
        $this->_appStateMock->expects($this->at(0))->method('getUpdateMode')->will($this->returnValue(true));
        $this->_storeMock->expects($this->any())->method('setId')->will($this->returnSelf());
        $this->_storeMock->expects($this->any())->method('setCode');
        $this->_storeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_storeMock));
        $this->_model->getStore();

        $this->_appStateMock->expects($this->at(1))->method('getUpdateMode')->will($this->returnValue(false));
        $this->assertSame($this->_storeMock, $this->_model->getStore($this->_storeMock));

        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertSame($this->_storeMock, $this->_model->getStore(true));
    }

    public function testReinitStores()
    {
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
    }

    public function testGetStore()
    {
        $this->_storeFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_storeMock));
        $this->_storeMock->expects($this->at(0))->method('load')->with(1);
        $this->_storeMock->expects($this->at(1))->method('getCode')->will($this->returnValue('code'));
        $this->_storeMock->expects($this->at(2))->method('__call')->will($this->returnValue(1));
        $this->assertSame($this->_storeMock, $this->_model->getStore(1));
    }

    public function testGetStores()
    {
        $expected = [1 => $this->_storeMock];
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertSame($expected, $this->_model->getStores());

        $expected = ['store_code' => $this->_storeMock];
        $this->assertSame($expected, $this->_model->getStores(false, true));
    }

    public function testHasSingleStore()
    {
        $this->assertNull($this->_model->hasSingleStore());
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertTrue($this->_model->hasSingleStore());
    }

    public function testIsSingleStoreMode()
    {
        $this->_scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->will($this->returnValue(true));
        $this->assertFalse($this->_model->isSingleStoreMode());

        $this->_scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->will($this->returnValue(false));
        $this->assertFalse($this->_model->isSingleStoreMode());

        $this->_scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->will($this->returnValue(true));
        //set $this->hasSingleStore() to true
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertTrue($this->_model->isSingleStoreMode());

        $this->_scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->will($this->returnValue(false));
        $this->assertTrue($this->_model->isSingleStoreMode());
    }

    public function testSetIsSingleStoreModeAllowed()
    {
        $this->prepareMockForReinit();
        $this->_model->setIsSingleStoreModeAllowed(false);
        $this->_model->reinitStores();
        $this->assertFalse($this->_model->hasSingleStore());

        $this->_model->setIsSingleStoreModeAllowed(true);
        $this->_model->reinitStores();
        $this->assertTrue($this->_model->hasSingleStore());
    }

    public function testGetDefaultStoreView()
    {
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->_websiteMock->expects($this->any())->method('__call')->will($this->returnValue(true));
        $this->_websiteMock->expects($this->any())->method('getDefaultGroupId')->will($this->returnValue(1));
        $this->_groupMock
            ->expects($this->any())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->_storeMock));
        $this->assertSame($this->_storeMock, $this->_model->getDefaultStoreView());
    }

    public function testGetAnyStoreViewDefaultStoreView()
    {
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->_websiteMock->expects($this->any())->method('__call')->will($this->returnValue(true));
        $this->_websiteMock->expects($this->any())->method('getDefaultGroupId')->will($this->returnValue(1));
        $this->_groupMock
            ->expects($this->any())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->_storeMock));
        $this->assertSame($this->_storeMock, $this->_model->getDefaultStoreView());
    }

    public function testClearWebsiteCache()
    {
        $this->prepareMockForReinit();
        $this->_model->reinitStores();
        $this->assertSame([1 => $this->_websiteMock], $this->_model->getWebsites());
        $this->_websiteMock->expects($this->at(0))->method('__call')->will($this->returnValue(1));
        $this->_websiteMock->expects($this->at(1))->method('getCode')->will($this->returnValue('website_code'));
        $this->_model->clearWebsiteCache(1);
        $this->assertEmpty($this->_model->getWebsites());
    }

    /**
     * Mock for Iterator class
     */
    protected function mockIterator(\PHPUnit_Framework_MockObject_MockObject $mockObject, array $items)
    {
        $mockObject
            ->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($items)));
    }
}
