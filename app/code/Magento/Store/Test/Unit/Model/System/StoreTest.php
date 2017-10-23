<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model\System;

class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var int
     */
    protected $groupId = 2;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->groupMock = $this->getMockBuilder(\Magento\Store\Model\Group::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->storeManagerMock = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->groupMock->expects($this->any())->method('getStores')->willReturn([$this->storeMock]);
        $this->groupMock->expects($this->atLeastOnce())->method('getId')->willReturn($this->groupId);
        $this->websiteMock->expects($this->atLeastOnce())->method('getGroups')->willReturn([$this->groupMock]);
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStores')->willReturn([$this->storeMock]);
        $this->model = $objectManager->getObject(
            \Magento\Store\Model\System\Store::class,
            ['storeManager' => $this->storeManagerMock]
        );
    }

    /**
     * @dataProvider getStoresStructureDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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

    public function getStoresStructureDataProvider()
    {
        $websiteName = 'website';
        $groupName = 'group';
        $storeName = 'store';
        $storeId = 1;
        $groupId = $this->groupId;
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
     * @dataProvider getStoreValuesForFormDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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
        $this->websiteMock->expects($this->any())->method('getId')->willReturn($websiteId);
        $this->websiteMock->expects($this->any())->method('getName')->willReturn($websiteName);
        $this->groupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $this->groupMock->expects($this->any())->method('getName')->willReturn($groupName);
        $this->groupMock->expects($this->any())->method('getWebsiteId')->willReturn($groupWebsiteId);
        $this->storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->storeMock->expects($this->any())->method('getName')->willReturn($storeName);
        $this->storeMock->expects($this->any())->method('getGroupId')->willReturn($storeGroupId);

        $this->model->setIsAdminScopeAllowed(true);
        $this->assertEquals(
            $this->model->getStoreValuesForForm($empty, $all),
            $expectedResult
        );
    }

    public function getStoreValuesForFormDataProvider()
    {
        $websiteName = 'website';
        $groupName = 'group';
        $storeName = 'store';
        $storeId = 1;
        $groupId = $this->groupId;
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
                    ['label' => '', 'value' => ''],
                    ['label' => __('All Store Views'), 'value' => 0],
                    ['label' => $websiteName, 'value' => []],
                    [
                        'label' => str_repeat($nonEscapableNbspChar, 4) . $groupName,
                        'value' => [
                            ['label' => str_repeat($nonEscapableNbspChar, 4) . $storeName, 'value' => $storeId]
                        ]
                    ],
                ]
            ],
        ];
    }
}
