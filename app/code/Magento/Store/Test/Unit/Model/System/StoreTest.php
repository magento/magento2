<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\System;

use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\System\Store as sysStore;

class StoreTest extends TestCase
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Website|MockObject
     */
    protected $websiteMock;

    /**
     * @var Group|MockObject
     */
    protected $groupMock;

    /**
     * @var \Magento\Store\Model\Store|MockObject
     */
    protected $storeMock;

    /**
     * @var int
     */
    protected static $groupId = 2;

    /**
     * @var int
     */
    protected $groupWebsiteId = 3;

    protected function setUp(): void
    {
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->groupMock->expects($this->any())->method('getStores')->willReturn([$this->storeMock]);
        $this->groupMock->expects($this->any())->method('getId')->willReturn(self::$groupId);
        $this->groupWebsiteId = 3;
        $this->groupMock->expects($this->any())->method('getWebsiteId')->willReturnCallback(function () {
            return $this->groupWebsiteId;
        });
        $this->websiteMock->expects($this->any())->method('getId')->willReturn(3);
        $this->websiteMock->expects($this->any())->method('getGroups')->willReturn([$this->groupMock]);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getWebsites')
            ->willReturn([3 => $this->websiteMock]);
        $this->storeManagerMock->expects($this->any())->method('getGroups')->willReturn([$this->groupMock]);
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStores')
            ->willReturn([1 => $this->storeMock]);
        $this->model = new sysStore($this->storeManagerMock);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    #[DataProvider('getStoresStructureDataProvider')]
    public function testGetStoresStructure(
        $isAll,
        $storeId,
        $groupId,
        $websiteId,
        $storeName,
        $groupName,
        $websiteName,
        $storeIds,
        $groupIds,
        $websiteIds,
        $expectedResult
    ) {
        $this->websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $this->websiteMock->expects($this->any())->method('getName')->willReturn($websiteName);
        $this->groupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $this->groupMock->expects($this->any())->method('getName')->willReturn($groupName);
        $this->storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->storeMock->expects($this->any())->method('getName')->willReturn($storeName);
        $this->assertEquals(
            $this->model->getStoresStructure($isAll, $storeIds, $groupIds, $websiteIds),
            $expectedResult
        );
    }

    /**
     * @return array
     */
    public static function getStoresStructureDataProvider()
    {
        $websiteName = 'website';
        $groupName = 'group';
        $storeName = 'store';
        $storeId = 1;
        $groupId = self::$groupId;
        $websiteId = 3;

        return [
            'empty' => [
                'isAll' => false,
                'storeId' => $storeId,
                'groupId' => $groupId,
                'websiteId' => $websiteId,
                'storeName' => $storeName,
                'groupName' => $groupName,
                'websiteName' => $websiteName,
                'storeIds' => [0],
                'groupIds' => [0],
                'websiteIds' => [0],
                'expectedResult' => []
            ],
            'allAndWebsiteAndGroupAndStore' => [
                'isAll' => true,
                'storeId' => $storeId,
                'groupId' => $groupId,
                'websiteId' => $websiteId,
                'storeName' => $storeName,
                'groupName' => $groupName,
                'websiteName' => $websiteName,
                'storeIds' => [$storeId],
                'groupIds' => [$groupId],
                'websiteIds' => [$websiteId],
                'expectedResult' => [
                    ['value' => 0, 'label' => __('All Store Views')],
                    $websiteId => [
                        'value' => $websiteId,
                        'label' => $websiteName,
                        'children' => [
                            $groupId => [
                                'value' => $groupId,
                                'label' => $groupName,
                                'children' => [
                                    $storeId => ['value' => $storeId, 'label' => $storeName]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'allAndWebsiteWithoutStores' => [
                'isAll' => true,
                'storeId' => $storeId,
                'groupId' => $groupId,
                'websiteId' => $websiteId,
                'storeName' => $storeName,
                'groupName' => $groupName,
                'websiteName' => $websiteName,
                'storeIds' => [0],
                'groupIds' => [$groupId],
                'websiteIds' => [$websiteId],
                'expectedResult' => [
                    ['value' => 0, 'label' => __('All Store Views')]
                ]
            ],

        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    #[DataProvider('getStoreValuesForFormDataProvider')]
    public function testGetStoreValuesForForm(
        $empty,
        $all,
        $storeId,
        $groupId,
        $websiteId,
        $storeName,
        $groupName,
        $websiteName,
        $storeGroupId,
        $groupWebsiteId,
        $expectedResult
    ) {
        $this->groupWebsiteId = $groupWebsiteId;
        $this->websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $this->websiteMock->expects($this->any())->method('getName')->willReturn($websiteName);
        $this->groupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $this->groupMock->expects($this->any())->method('getName')->willReturn($groupName);
        $this->groupMock->expects($this->any())->method('getStores')->willReturn([$this->storeMock]);
        $this->storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->storeMock->expects($this->any())->method('getName')->willReturn($storeName);
        $this->storeMock->expects($this->any())->method('getGroupId')->willReturn($storeGroupId);
        $this->storeManagerMock->expects($this->any())
            ->method('getWebsites')
            ->willReturn([$websiteId => $this->websiteMock]);
        $this->storeManagerMock->expects($this->any())
            ->method('getGroups')
            ->willReturn([$this->groupMock]);
        $this->storeManagerMock->expects($this->any())
            ->method('getStores')
            ->willReturn([$storeId => $this->storeMock]);
        $this->model->reload();
        $this->model->setIsAdminScopeAllowed(true);
        $this->assertEquals(
            $this->model->getStoreValuesForForm($empty, $all),
            $expectedResult
        );
    }

    /**
     * @return array
     */
    public static function getStoreValuesForFormDataProvider()
    {
        $websiteName = 'website';
        $groupName = 'group';
        $storeName = 'store';
        $storeId = 1;
        $groupId = self::$groupId;
        $websiteId = 3;
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');

        return [
            'showNothing1' => [
                'empty' => false,
                'all' => false,
                'storeId' => $storeId,
                'groupId' => $groupId,
                'websiteId' => $websiteId,
                'storeName' => $storeName,
                'groupName' => $groupName,
                'websiteName' => $websiteName,
                'storeGroupId' => $groupId+1,
                'groupWebsiteId' => $websiteId,
                'expectedResult' => []
            ],
            'showNothing2' => [
                'empty' => false,
                'all' => false,
                'storeId' => $storeId,
                'groupId' => $groupId,
                'websiteId' => $websiteId,
                'storeName' => $storeName,
                'groupName' => $groupName,
                'websiteName' => $websiteName,
                'storeGroupId' => $groupId,
                'groupWebsiteId' => $websiteId+1,
                'expectedResult' => []
            ],
            'showEmptyAndAllAndWebsiteAndGroup' => [
                'empty' => true,
                'all' => true,
                'storeId' => $storeId,
                'groupId' => $groupId,
                'websiteId' => $websiteId,
                'storeName' => $storeName,
                'groupName' => $groupName,
                'websiteName' => $websiteName,
                'storeGroupId' => $groupId,
                'groupWebsiteId' => $websiteId,
                'expectedResult' => [
                    ['label' => '', 'value' => '','__disableTmpl' => true],
                    ['label' => __('All Store Views'), 'value' => 0,'__disableTmpl' => true],
                    ['label' => $websiteName, 'value' => [],'__disableTmpl' => true],
                    [
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $groupName,
                        'value' => [
                            ['label' => str_repeat($nonEscapableNbspChar, 4) . $storeName, 'value' => $storeId]
                        ],
                        '__disableTmpl' => true
                    ],
                ]
            ],
        ];
    }
}
