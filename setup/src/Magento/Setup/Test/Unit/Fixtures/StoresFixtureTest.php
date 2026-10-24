<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\Config;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Fixtures\StoresFixture;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoresFixtureTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var StoresFixture
     */
    private $model;

    /**
     * @var StoreManager
     */
    private $storeManagerMock;

    /**
     * @var ManagerInterface
     */
    private $eventManagerMock;

    /**
     * @var CategoryFactory
     */
    private $categoryFactoryMock;

    /**
     * @var Writer
     */
    private $scopeConfigMock;

    /**
     * @var Config
     */
    private $localeConfigMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getGroup',
                    'getGroups',
                    'getWebsite',
                    'getDefaultStoreView',
                    'getStore',
                    'getStores',
                ]
            )->getMock();

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $categoryMock = $this->createPartialMockWithReflection(
            CategoryInterface::class,
            [
                'getId', 'setId', 'getParentId', 'setParentId', 'getName', 'setName',
                'getIsActive', 'setIsActive', 'getPosition', 'setPosition', 'getLevel', 'setLevel',
                'getChildren', 'getCreatedAt', 'setCreatedAt', 'getUpdatedAt', 'setUpdatedAt',
                'getPath', 'setPath', 'getAvailableSortBy', 'setAvailableSortBy',
                'getIncludeInMenu', 'setIncludeInMenu', 'getExtensionAttributes', 'setExtensionAttributes',
                'getCustomAttribute', 'setCustomAttribute', 'getCustomAttributes', 'setCustomAttributes',
                'setDefaultSortBy', 'save'
            ]
        );

        $this->categoryFactoryMock->expects($this->exactly(5))
            ->method('create')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('setName')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('setPath')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('setLevel')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('setAvailableSortBy')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('setDefaultSortBy')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('setIsActive')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('getId')
            ->willReturn($categoryMock);

        $categoryMock->expects($this->exactly(5))
            ->method('save')
            ->willReturn($categoryMock);

        $this->localeConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllowedLocales'])
            ->getMock();

        $this->localeConfigMock->expects($this->once())
            ->method('getAllowedLocales')
            ->willReturn(['en_US']);

        $this->scopeConfigMock = $this->getMockBuilder(Writer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock = $this->createPartialMockWithReflection(
            StoreInterface::class,
            [
                'getId', 'setId', 'getCode', 'setCode', 'getName', 'setName',
                'getWebsiteId', 'setWebsiteId', 'getStoreGroupId', 'setIsActive',
                'getIsActive', 'setStoreGroupId', 'getExtensionAttributes', 'setExtensionAttributes',
                'addData', 'save'
            ]
        );

        $storeMock->expects($this->exactly(11))
            ->method('getId')
            ->willReturn(1);

        $storeMock->expects($this->exactly(11))
            ->method('addData')
            ->willReturnCallback(function ($arg) use ($storeMock) {
                if (isset($arg['code'])) {
                    return $storeMock;
                }
            });

        $storeGroupMock = $this->createPartialMockWithReflection(
            GroupInterface::class,
            [
                'getId', 'setId', 'getWebsiteId', 'setWebsiteId', 'getRootCategoryId', 'setRootCategoryId',
                'getDefaultStoreId', 'setDefaultStoreId', 'getName', 'setName', 'getCode', 'setCode',
                'getExtensionAttributes', 'setExtensionAttributes', 'addData', 'save'
            ]
        );

        $storeGroupMock->expects($this->exactly(11))
            ->method('getId')
            ->willReturn(1);

        $storeGroupMock->expects($this->exactly(5))
            ->method('addData')
            ->willReturnCallback(function ($arg) use ($storeGroupMock) {
                if ($arg['code'] == 'store_group_2' || $arg['code'] == 'store_group_3') {
                    return $storeGroupMock;
                }
            });

        $websiteMock = $this->createPartialMockWithReflection(
            WebsiteInterface::class,
            [
                'getId', 'setId', 'getCode', 'setCode', 'getName', 'setName',
                'getDefaultGroupId', 'setDefaultGroupId', 'getExtensionAttributes', 'setExtensionAttributes',
                'addData', 'save'
            ]
        );

        $websiteMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(1);

        $websiteMock->expects($this->exactly(2))
            ->method('addData')
            ->willReturnCallback(function ($arg) use ($storeGroupMock) {
                if ($arg['code'] == 'website_2' || $arg['code'] == 'website_3') {
                    return $storeGroupMock;
                }
            });

        $this->storeManagerMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$storeGroupMock]);

        $this->storeManagerMock->expects($this->once())
            ->method('getGroup')
            ->willReturn($storeGroupMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);

        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);

        $this->storeManagerMock->expects($this->once())
            ->method('getDefaultStoreView')
            ->willReturn($storeMock);

        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getValue')
            ->willReturnMap([
                ['websites', 1, 3],
                ['store_groups', 1, 6],
                ['store_views', 1, 12],
                ['assign_entities_to_all_websites', false]
            ]);

        $this->model = new StoresFixture(
            $this->fixtureModelMock,
            $this->storeManagerMock,
            $this->eventManagerMock,
            $this->categoryFactoryMock,
            $this->localeConfigMock,
            $this->scopeConfigMock
        );

        $this->model->execute();
    }
}
