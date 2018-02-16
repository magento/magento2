<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Block\Store;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class SwitcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Block\Store\Switcher
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\StoreFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeFactoryMock;

    /**
     * @var \Magento\Store\Model\GroupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeGroupFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeFactoryMock = $this->getMockBuilder('Magento\Store\Model\StoreFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeGroupFactoryMock = $this->getMockBuilder('Magento\Store\Model\GroupFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->loadMocks();
        $this->model = $objectManager->getObject(
            'Magento\Store\Block\Store\Switcher',
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
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getLocaleCode', 'isActive', 'getId', 'getGroupId', 'getCollection'])
            ->getMock();
        $groupMock = $this->getMockBuilder('Magento\Store\Model\Group')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var AbstractCollection|\PHPUnit_Framework_MockObject_MockObject */
        $storeCollectionMock =
            $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection')
                ->disableOriginalConstructor()
                ->setMethods(['addWebsiteFilter', 'load'])
                ->getMockForAbstractClass();
        /** @var AbstractCollection|\PHPUnit_Framework_MockObject_MockObject */
        $groupCollectionMock =
            $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection')
                ->disableOriginalConstructor()
                ->setMethods(['addWebsiteFilter', 'load'])
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
