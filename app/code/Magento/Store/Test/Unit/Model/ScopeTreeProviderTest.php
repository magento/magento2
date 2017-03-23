<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeTreeProvider;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Website;

class ScopeTreeProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ScopeTreeProvider */
    protected $model;

    /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ScopeTreeProvider($this->storeManagerMock);
    }

    public function testGet()
    {
        $websiteId = 1;
        $groupId = 2;
        $storeId = 3;
        $storeData = [
            'scope' => ScopeInterface::SCOPE_STORES,
            'scope_id' => $storeId,
            'scopes' => [],
        ];
        $groupData = [
            'scope' => ScopeInterface::SCOPE_GROUP,
            'scope_id' => $groupId,
            'scopes' => [$storeData, $storeData, $storeData],
        ];
        $websiteData = [
            'scope' => ScopeInterface::SCOPE_WEBSITES,
            'scope_id' => $websiteId,
            'scopes' => [$groupData, $groupData],
        ];
        $result = [
            'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            'scope_id' => null,
            'scopes' => [$websiteData],
        ];

        /** @var Website|\PHPUnit_Framework_MockObject_MockObject $websiteMock */
        $websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock->expects($this->any())
            ->method('getId')
            ->willReturn($websiteId);

        /** @var Group|\PHPUnit_Framework_MockObject_MockObject $groupMock */
        $groupMock = $this->getMockBuilder(\Magento\Store\Model\Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMock->expects($this->any())
            ->method('getId')
            ->willReturn($groupId);

        /** @var Store|\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->storeManagerMock->expects($this->any())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $websiteMock->expects($this->any())
            ->method('getGroups')
            ->willReturn([$groupMock, $groupMock]);

        $groupMock->expects($this->any())
            ->method('getStores')
            ->willReturn([$storeMock, $storeMock, $storeMock]);

        $this->assertEquals($result, $this->model->get());
    }
}
