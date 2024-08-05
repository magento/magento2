<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Processor\Create;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CreateTest extends TestCase
{
    /**
     * @var DataDifferenceCalculator|MockObject
     */
    private $dataDifferenceCalculatorMock;

    /**
     * @var WebsiteFactory|MockObject
     */
    private $websiteFactoryMock;

    /**
     * @var GroupFactory|MockObject
     */
    private $groupFactoryMock;

    /**
     * @var StoreFactory|MockObject
     */
    private $storeFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var AbstractDb|MockObject
     */
    private $abstractDbMock;

    /**
     * @var Website|MockObject
     */
    private $websiteMock;

    /**
     * @var Group|MockObject
     */
    private $groupMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Create
     */
    private $processor;

    /**
     * @var array
     */
    private $websites = [];

    /**
     * @var array
     */
    private $trimmedWebsite = [];

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $trimmedGroup = [];

    /**
     * @var array
     */
    private $stores = [];

    /**
     * @var array
     */
    private $trimmedStore = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->initTestData();

        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteFactoryMock = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->groupFactoryMock = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'load', 'addCommitCallback'])
            ->getMockForAbstractClass();
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData', 'getResource', 'setDefaultGroupId'])
            ->getMock();
        $this->groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getResource', 'getId', 'setData', 'setRootCategoryId',
                'getDefaultStoreId', 'setDefaultStoreId', 'setWebsite'
            ])
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->addMethods(['getStoreId'])
            ->onlyMethods(['setData', 'getResource', 'setGroup', 'setWebsite'])
            ->getMock();
        $this->websiteFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->websiteMock);
        $this->groupFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->groupMock);
        $this->storeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->storeMock);

        $this->processor = new Create(
            $this->dataDifferenceCalculatorMock,
            $this->eventManagerMock,
            $this->websiteFactoryMock,
            $this->groupFactoryMock,
            $this->storeFactoryMock
        );
    }

    private function initTestData()
    {
        $this->websites = [
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1',
            ],
        ];
        $this->trimmedWebsite = [
            'code' => 'base',
            'name' => 'Main Website',
            'sort_order' => '0',
            'is_default' => '1',
        ];
        $this->groups = [
            1 => [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Default',
                'root_category_id' => '1',
                'default_store_id' => '1',
                'code' => 'default',
            ],
            2 => [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Default1',
                'default_store_id' => '1',
                'code' => 'default1',
            ]
        ];
        $this->trimmedGroup =
            [
                0 => [
                    'name' => 'Default',
                    'root_category_id' => '1',
                    'code' => 'default',
                    'default_store_id' => '1',
                ],
                1 => [
                    'name' => 'Default1',
                    'root_category_id' => '0',
                    'code' => 'default1',
                    'default_store_id' => '1'
                ]
            ];
        $this->stores = [
            'default' => [
                'store_id' => '1',
                'code' => 'default',
                'website_id' => '1',
                'group_id' => '1',
                'name' => 'Default Store View',
                'sort_order' => '0',
                'is_active' => '1',
            ],
        ];
        $this->trimmedStore = [
            'code' => 'default',
            'name' => 'Default Store View',
            'sort_order' => '0',
            'is_active' => '1',
        ];
        $this->data = [
            'websites' => $this->websites,
            'groups' => $this->groups,
            'stores' => $this->stores,
        ];
    }

    public function testRunWebsite()
    {
        $groupId = 1;
        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, $this->websites, $this->websites],
            ]);

        $this->websiteMock->expects($this->once())
            ->method('setData')
            ->with($this->trimmedWebsite)
            ->willReturnSelf();
        $this->websiteMock->expects($this->exactly(3))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->websiteMock->expects($this->once())
            ->method('setDefaultGroupId')
            ->with($groupId);

        $this->groupMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->groupMock->expects($this->once())
            ->method('getId')
            ->willReturn($groupId);

        $this->abstractDbMock->expects($this->once())
            ->method('addCommitCallback')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $this->abstractDbMock->expects($this->exactly(2))
            ->method('save')
            ->with($this->websiteMock)
            ->willReturnSelf();

        $this->processor->run($this->data);
    }

    public function testRunGroup()
    {
        $defaultStoreId = 1;
        $storeId = 1;
        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, $this->websites, []],
                [ScopeInterface::SCOPE_GROUPS, $this->groups, $this->groups],
            ]);

        $this->websiteMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);

        $this->groupMock->expects($this->exactly(2))
            ->method('setData')
            ->willReturnCallback(function ($arg1)  {
                if ($arg1 == $this->equalTo($this->trimmedGroup[0])) {
                    return $this;
                } elseif ($arg1 == $this->equalTo($this->trimmedGroup[1])) {
                    return $this;
                }
            });
        $this->groupMock->expects($this->exactly(6))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->groupMock->expects($this->exactly(2))
            ->method('getDefaultStoreId')
            ->willReturn($defaultStoreId);
        $this->groupMock->expects($this->exactly(2))
            ->method('setDefaultStoreId')
            ->with($storeId);
        $this->groupMock->expects($this->exactly(2))
            ->method('setWebsite')
            ->with($this->websiteMock);

        $this->storeMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->storeMock->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->abstractDbMock->expects($this->any())
            ->method('load')
            ->willReturnCallback(function ($arg1, $arg2, $arg3)  {
                if ($arg1 == $this->websiteMock && $arg2 == 'base' && $arg3 == 'code') {
                    return $this;
                } elseif ($arg1 == $this->storeMock && $arg2 == 'default' && $arg3 == 'code') {
                    return $this;
                }
            });
        $this->abstractDbMock->expects($this->exactly(4))
            ->method('save')
            ->with($this->groupMock)
            ->willReturnSelf();
        $this->abstractDbMock->expects($this->exactly(2))
            ->method('addCommitCallback')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $this->processor->run($this->data);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRunStore()
    {
        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, $this->websites, []],
                [ScopeInterface::SCOPE_GROUPS, $this->groups, []],
                [ScopeInterface::SCOPE_STORES, $this->stores, $this->stores],
            ]);

        $this->websiteMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDbMock);

        $this->groupMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDbMock);

        $this->abstractDbMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(function ($arg1, $arg2, $arg3)  {
                if ($arg1 == $this->groupMock && $arg2 == 'default' && $arg3 == 'code') {
                    return $this;
                } elseif ($arg1 == $this->websiteMock && $arg2 == 'base' && $arg3 == 'code') {
                    return $this;
                }
            });

        $this->storeMock->expects($this->once())
            ->method('setData')
            ->with($this->trimmedStore)
            ->willReturnSelf();
        $this->storeMock->expects($this->exactly(3))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->storeMock->expects($this->once())
            ->method('setGroup')
            ->with($this->groupMock);
        $this->storeMock->expects($this->once())
            ->method('setWebsite')
            ->with($this->websiteMock);

        $this->abstractDbMock->expects($this->exactly(2))
            ->method('save')
            ->with($this->storeMock)
            ->willReturnSelf();
        $this->abstractDbMock->expects($this->once())
            ->method('addCommitCallback')
            ->willReturnCallback(function ($function) {
                return $function();
            });

        $this->processor->run($this->data);
    }

    public function testRunWithException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Some error');
        $data = [
            'websites' => [],
            'groups' => [],
            'stores' => [],
        ];

        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willThrowException(new \Exception('Some error'));

        $this->processor->run($data);
    }
}
