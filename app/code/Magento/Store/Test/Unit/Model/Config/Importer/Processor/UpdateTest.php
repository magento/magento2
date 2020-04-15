<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Processor\Update;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use Magento\Store\Model\Store;
use Magento\Framework\Event\ManagerInterface;

/**
 * Test for Update processor.
 *
 * @see Update
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Update
     */
    private $model;

    /**
     * @var Group|Mock
     */
    private $groupMock;

    /**
     * @var GroupResource|Mock
     */
    private $groupResourceMock;

    /**
     * @var Website|Mock
     */
    private $websiteMock;

    /**
     * @var WebsiteResource|Mock
     */
    private $websiteResourceMock;

    /**
     * @var DataDifferenceCalculator|Mock
     */
    private $dataDifferenceCalculatorMock;

    /**
     * @var WebsiteFactory|Mock
     */
    private $websiteFactoryMock;

    /**
     * @var StoreFactory|Mock
     */
    private $storeFactoryMock;

    /**
     * @var GroupFactory|Mock
     */
    private $groupFactoryMock;

    /**
     * @var Store|Mock
     */
    private $storeMock;

    /**
     * @var StoreResource|Mock
     */
    private $storeResourceMock;

    /**
     * @var ManagerInterface|Mock
     */
    private $eventManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteFactoryMock = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteResourceMock = $this->getMockBuilder(WebsiteResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupFactoryMock = $this->getMockBuilder(GroupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupResourceMock = $this->getMockBuilder(GroupResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeResourceMock = $this->getMockBuilder(StoreResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->storeMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->storeResourceMock);
        $this->websiteMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->websiteResourceMock);
        $this->websiteFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->websiteMock);

        $this->model = new Update(
            $this->dataDifferenceCalculatorMock,
            $this->websiteFactoryMock,
            $this->storeFactoryMock,
            $this->groupFactoryMock,
            $this->eventManagerMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRun()
    {
        $data = $this->getData();
        $updateData = $this->getData();

        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToUpdate')
            ->willReturnMap([
                [
                    ScopeInterface::SCOPE_GROUPS,
                    $data[ScopeInterface::SCOPE_GROUPS],
                    $updateData[ScopeInterface::SCOPE_GROUPS],
                ],
                [
                    ScopeInterface::SCOPE_WEBSITES,
                    $data[ScopeInterface::SCOPE_WEBSITES],
                    $updateData[ScopeInterface::SCOPE_WEBSITES],
                ],
                [
                    ScopeInterface::SCOPE_STORES,
                    $data[ScopeInterface::SCOPE_STORES],
                    $updateData[ScopeInterface::SCOPE_STORES],
                ],
            ]);
        $this->websiteMock->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($this->websiteResourceMock);
        $this->websiteMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'website_id' => '2',
                'code' => 'test',
                'name' => 'Main Test',
                'sort_order' => '0',
                'default_group_id' => '1',
                'is_default' => '0',
            ]);
        $this->websiteMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->willReturn('2');
        $this->websiteResourceMock->expects($this->exactly(3))
            ->method('load')
            ->with($this->websiteMock, 'test', 'code');
        $this->websiteMock->expects($this->any())
            ->method('setData')
            ->with($updateData[ScopeInterface::SCOPE_WEBSITES]['test']);
        $this->websiteResourceMock->expects($this->once())
            ->method('save')
            ->with($this->websiteMock);
        $this->groupFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($this->groupMock);
        $this->groupMock->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($this->groupResourceMock);
        $this->groupMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'group_id' => '2',
                'website_id' => '2',
                'name' => 'Changed Test Website Store',
                'root_category_id' => '2',
                'default_store_id' => '1',
                'code' => 'test_website_store',
            ]);
        $this->groupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn('2');
        $updateGroupData = $updateData[ScopeInterface::SCOPE_GROUPS][2];
        $updateGroupData['root_category_id'] = 2;
        $this->groupMock->expects($this->once())
            ->method('setData')
            ->with($updateGroupData);
        $this->storeFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->atLeastOnce())
            ->method('getResource')
            ->willReturn($this->storeResourceMock);
        $this->storeMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'store_id' => '2',
                'code' => 'test',
                'website_id' => '2',
                'group_id' => '2',
                'name' => 'Test Store View',
                'sort_order' => '0',
                'is_active' => '1',
            ]);
        $this->storeMock->expects($this->once())
            ->method('setData')
            ->with($updateData[ScopeInterface::SCOPE_STORES]['test']);
        $this->storeMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->model->run($data);
    }

    /**
     * @return array
     */
    private function getData()
    {
        return [
            ScopeInterface::SCOPE_WEBSITES => [
                'test' => [
                    'website_id' => '2',
                    'code' => 'test',
                    'name' => 'Changed Main Test',
                    'sort_order' => '0',
                    'default_group_id' => '2',
                    'is_default' => '0',
                ]
            ],
            ScopeInterface::SCOPE_GROUPS => [
                2 => [
                    'group_id' => '2',
                    'website_id' => '2',
                    'name' => 'Changed Test Website Store',
                    'root_category_id' => '3',
                    'default_store_id' => '2',
                    'code' => 'test_website_store',
                ],
            ],
            ScopeInterface::SCOPE_STORES => [
                'test' => [
                    'store_id' => '2',
                    'code' => 'test',
                    'website_id' => '2',
                    'group_id' => '2',
                    'name' => 'Changed Test Store View',
                    'sort_order' => '0',
                    'is_active' => '1',
                ]
            ],
        ];
    }

    /**
     */
    public function testRunWithException()
    {
        $this->expectException(\Magento\Framework\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Some exception');

        $data = [
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_STORES => []
        ];

        $this->dataDifferenceCalculatorMock->expects($this->once())
            ->method('getItemsToUpdate')
            ->willThrowException(new \Exception('Some exception'));

        $this->model->run($data);
    }
}
