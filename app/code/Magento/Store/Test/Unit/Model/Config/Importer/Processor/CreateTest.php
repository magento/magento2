<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Framework\Event\ManagerInterface;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataDifferenceCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataDifferenceCalculatorMock;

    /**
     * @var WebsiteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteFactoryMock;

    /**
     * @var GroupFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupFactoryMock;

    /**
     * @var StoreFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractDbMock;

    /**
     * @var Create
     */
    private $processor;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteFactoryMock = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupFactoryMock = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->abstractDbMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'load', 'addCommitCallback'])
            ->getMockForAbstractClass();

        $this->processor = new Create(
            $this->dataDifferenceCalculatorMock,
            $this->eventManagerMock,
            $this->websiteFactoryMock,
            $this->groupFactoryMock,
            $this->storeFactoryMock
        );
    }

    public function testRunWebsite()
    {
        $websites = [
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1',
            ],
        ];
        $trimmedWebsite = [
            'code' => 'base',
            'name' => 'Main Website',
            'sort_order' => '0',
            'is_default' => '1',
            'default_group_id' => '1',
        ];
        $data = [
            'websites' => $websites,
            'stores' => [],
        ];

        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, $websites, $websites],
            ]);

        /** @var Website|\PHPUnit_Framework_MockObject_MockObject $websiteMock */
        $websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getResource'])
            ->getMock();
        $websiteMock->expects($this->once())
            ->method('setData')
            ->with($trimmedWebsite)
            ->willReturnSelf();
        $websiteMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);

        $this->websiteFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($websiteMock);
        $this->abstractDbMock->expects($this->once())
            ->method('save')
            ->with($websiteMock)
            ->willReturnSelf();

        $this->processor->run($data);
    }

    public function testRunGroup()
    {
        $websites = [
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1',
            ],
        ];
        $groups = [
            1 => [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Default',
                'root_category_id' => '1',
                'default_store_id' => '1',
                'code' => 'default',
            ]
        ];
        $trimmedGroup = [
            'name' => 'Default',
            'root_category_id' => '1',
            'code' => 'default',
            'default_store_id' => '1',
        ];
        $data = [
            'websites' => $websites,
            'groups' => $groups,
        ];

        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, $websites, []],
                [ScopeInterface::SCOPE_GROUPS, $groups, $groups],
            ]);

        /** @var Website|\PHPUnit_Framework_MockObject_MockObject $websiteMock */
        $websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResource'])
            ->getMock();
        $websiteMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->websiteFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($websiteMock);
        $this->abstractDbMock->expects($this->once())
            ->method('load')
            ->with($websiteMock, 'base', 'code')
            ->willReturnSelf();

        /** @var Group|\PHPUnit_Framework_MockObject_MockObject $groupMock */
        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getResource', 'setWebsite', 'setRootCategoryId'])
            ->getMock();
        $groupMock->expects($this->once())
            ->method('setData')
            ->with($trimmedGroup)
            ->willReturnSelf();
        $groupMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $groupMock->expects($this->once())
            ->method('setRootCategoryId')
            ->with(0);

        $this->groupFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($groupMock);
        $this->abstractDbMock->expects($this->once())
            ->method('save')
            ->with($groupMock)
            ->willReturnSelf();
        $this->abstractDbMock->expects($this->once())
            ->method('addCommitCallback');

        $this->processor->run($data);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRunStore()
    {
        $websites = [
            'base' => [
                'website_id' => '1',
                'code' => 'base',
                'name' => 'Main Website',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '1',
            ],
        ];
        $groups = [
            1 => [
                'group_id' => '1',
                'website_id' => '1',
                'name' => 'Default',
                'root_category_id' => '1',
                'default_store_id' => '1',
                'code' => 'default',
            ]
        ];
        $stores = [
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
        $trimmedStore = [
            'code' => 'default',
            'name' => 'Default Store View',
            'sort_order' => '0',
            'is_active' => '1',
        ];
        $data = [
            'websites' => $websites,
            'groups' => $groups,
            'stores' => $stores,
        ];

        $this->dataDifferenceCalculatorMock->expects($this->any())
            ->method('getItemsToCreate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, $websites, []],
                [ScopeInterface::SCOPE_GROUPS, $groups, []],
                [ScopeInterface::SCOPE_STORES, $stores, $stores],
            ]);

        /** @var Website|\PHPUnit_Framework_MockObject_MockObject $websiteMock */
        $websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResource'])
            ->getMock();
        $websiteMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->websiteFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($websiteMock);

        /** @var Group|\PHPUnit_Framework_MockObject_MockObject $groupMock */
        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResource'])
            ->getMock();
        $groupMock->expects($this->once())
            ->method('getResource')
            ->willReturn($this->abstractDbMock);
        $this->groupFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($groupMock);

        $this->abstractDbMock->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive([$groupMock, 'default', 'code'], [$websiteMock, 'base', 'code'])
            ->willReturnSelf();

        /** @var Store|\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'getResource', 'setGroup', 'setWebsite'])
            ->getMock();
        $storeMock->expects($this->once())
            ->method('setData')
            ->with($trimmedStore)
            ->willReturnSelf();
        $storeMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($this->abstractDbMock);

        $this->storeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($storeMock);
        $this->abstractDbMock->expects($this->once())
            ->method('save')
            ->with($storeMock)
            ->willReturnSelf();
        $this->abstractDbMock->expects($this->once())
            ->method('addCommitCallback');

        $this->processor->run($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Some error
     */
    public function testRunWithException()
    {
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
