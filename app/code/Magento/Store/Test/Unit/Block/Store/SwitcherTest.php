<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Block\Store;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Block\Store\Switcher;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwitcherTest extends TestCase
{
    /**
     * @var Switcher
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var StoreFactory|MockObject
     */
    protected $storeFactoryMock;

    /**
     * @var GroupFactory|MockObject
     */
    protected $storeGroupFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeGroupFactoryMock = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->loadMocks();
        $this->model = $objectManager->getObject(
            Switcher::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'storeManager' => $this->storeManagerMock,
                'storeFactory' => $this->storeFactoryMock,
                'storeGroupFactory' => $this->storeGroupFactoryMock,
            ]
        );
    }

    public function testGetStoreCount()
    {
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn('en_US');
        $this->assertEquals(1, $this->model->getStoreCount());
    }

    public function testGetStoreCountWithNotEqualLocale()
    {
        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn('de_DE');
        $this->assertEquals(0, $this->model->getStoreCount());
    }

    protected function loadMocks()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLocaleCode', 'setLocaleCode'])
            ->onlyMethods(['isActive', 'getId', 'getGroupId', 'getCollection'])
            ->getMock();
        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCollection', 'getId'])
            ->getMock();
        /** @var AbstractCollection|MockObject */
        $storeCollectionMock =
            $this->getMockBuilder(AbstractCollection::class)
                ->disableOriginalConstructor()
                ->addMethods(['addWebsiteFilter'])
                ->onlyMethods(['load'])
                ->getMockForAbstractClass();
        /** @var AbstractCollection|MockObject */
        $groupCollectionMock =
            $this->getMockBuilder(AbstractCollection::class)
                ->disableOriginalConstructor()
                ->addMethods(['addWebsiteFilter'])
                ->onlyMethods(['load'])
                ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->storeFactoryMock->expects($this->any())->method('create')->willReturn($storeMock);
        $this->storeGroupFactoryMock->expects($this->any())->method('create')->willReturn($groupMock);
        $storeMock->expects($this->any())->method('getCollection')->willReturn($storeCollectionMock);
        $groupMock->expects($this->any())->method('getCollection')->willReturn($groupCollectionMock);
        $groupMock->expects($this->any())->method('getId')->willReturn(1);
        $storeMock->expects($this->any())->method('isActive')->willReturn(true);
        $storeMock->expects($this->atLeastOnce())->method('getLocaleCode')->willReturn('en_US');
        $storeMock->expects($this->any())->method('getGroupId')->willReturn(1);
        $storeMock->expects($this->any())->method('setLocaleCode');
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $storeCollectionMock->expects($this->any())->method('addWebsiteFilter')->willReturn([$storeMock]);
        $groupCollectionMock->expects($this->any())->method('addWebsiteFilter')->willReturn([$groupMock]);
    }
}
