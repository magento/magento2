<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeFallbackResolver;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ScopeFallbackResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var ScopeFallbackResolver */
    protected $model;

    /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->getMockForAbstractClass();

        $this->model = new ScopeFallbackResolver($this->storeManagerMock);
    }

    /**
     * @param string $scope
     * @param int $scopeId
     * @param bool $forConfig
     * @param int $websiteId
     * @param int $groupId
     * @param array $result
     *
     * @dataProvider dataProviderGetFallbackScope
     */
    public function testGetFallbackScope($scope, $scopeId, $forConfig, $websiteId, $groupId, $result)
    {
        /** @var GroupInterface|\PHPUnit_Framework_MockObject_MockObject $groupMock */
        $groupMock = $this->getMockBuilder('Magento\Store\Api\Data\GroupInterface')
            ->getMockForAbstractClass();
        $groupMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        /** @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMockBuilder('Magento\Store\Api\Data\StoreInterface')
            ->getMockForAbstractClass();
        $storeMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $storeMock->expects($this->any())
            ->method('getStoreGroupId')
            ->willReturn($groupId);

        $this->storeManagerMock->expects($this->any())
            ->method('getGroup')
            ->with($scopeId)
            ->willReturn($groupMock);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->with($scopeId)
            ->willReturn($storeMock);

        $this->assertEquals($result, $this->model->getFallbackScope($scope, $scopeId, $forConfig));
    }

    /**
     * @return array
     */
    public function dataProviderGetFallbackScope()
    {
        return [
            [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, true, null, null, [null, null]],
            [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0, false, 1, 2, [null, null]],
            [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 1, false, 0, 0, [null, null]],
            [ScopeInterface::SCOPE_WEBSITE, 1, true, 0, 0, [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null]],
            [ScopeInterface::SCOPE_WEBSITE, 2, false, null, null, [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null]],
            [ScopeInterface::SCOPE_WEBSITES, 3, true, 1, null, [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null]],
            [ScopeInterface::SCOPE_WEBSITES, 4, false, 0, null, [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null]],
            [ScopeInterface::SCOPE_GROUP, 1, true, 1, null, [ScopeInterface::SCOPE_WEBSITES, 1]],
            [ScopeInterface::SCOPE_GROUP, 2, false, 2, 3, [ScopeInterface::SCOPE_WEBSITES, 2]],
            [ScopeInterface::SCOPE_STORE, 1, true, 1, null, [ScopeInterface::SCOPE_WEBSITES, 1]],
            [ScopeInterface::SCOPE_STORE, 3, true, 1, 2, [ScopeInterface::SCOPE_WEBSITES, 1]],
            [ScopeInterface::SCOPE_STORE, 2, false, null, 1, [ScopeInterface::SCOPE_GROUP, 1]],
            [ScopeInterface::SCOPE_STORE, 4, false, 3, 2, [ScopeInterface::SCOPE_GROUP, 2]],
            [ScopeInterface::SCOPE_STORES, 1, true, 5, null, [ScopeInterface::SCOPE_WEBSITES, 5]],
            [ScopeInterface::SCOPE_STORES, 3, true, 6, 0, [ScopeInterface::SCOPE_WEBSITES, 6]],
            [ScopeInterface::SCOPE_STORES, 2, false, null, 7, [ScopeInterface::SCOPE_GROUP, 7]],
            [ScopeInterface::SCOPE_STORES, 4, false, 0, 8, [ScopeInterface::SCOPE_GROUP, 8]],
        ];
    }
}
