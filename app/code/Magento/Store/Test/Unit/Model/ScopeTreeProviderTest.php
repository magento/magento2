<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\ScopeTreeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Store\Model\ScopeTreeProvider
 */
class ScopeTreeProviderTest extends TestCase
{
    /**
     * @var ScopeTreeProvider
     */
    private $model;

    /**
     * @var MockObject|WebsiteRepositoryInterface
     */
    private $websiteRepositoryMock;

    /**
     * @var MockObject|GroupRepositoryInterface
     */
    private $groupRepositoryMock;

    /**
     * @var MockObject|StoreRepositoryInterface
     */
    private $storeRepositoryMock;

    protected function setUp(): void
    {
        $this->websiteRepositoryMock = $this->getMockForAbstractClass(WebsiteRepositoryInterface::class);
        $this->groupRepositoryMock = $this->getMockForAbstractClass(GroupRepositoryInterface::class);
        $this->storeRepositoryMock = $this->getMockForAbstractClass(StoreRepositoryInterface::class);

        $this->model = new ScopeTreeProvider(
            $this->websiteRepositoryMock,
            $this->groupRepositoryMock,
            $this->storeRepositoryMock
        );
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

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $websiteMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($websiteId);
        $this->websiteRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([$websiteMock]);

        $groupMock = $this->getMockForAbstractClass(GroupInterface::class);
        $groupMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $groupMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $this->groupRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([$groupMock, $groupMock]);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($storeId);
        $storeMock->expects($this->atLeastOnce())
            ->method('getStoreGroupId')
            ->willReturn($groupId);
        $this->storeRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn([$storeMock, $storeMock, $storeMock]);

        $this->assertEquals($result, $this->model->get());
    }
}
