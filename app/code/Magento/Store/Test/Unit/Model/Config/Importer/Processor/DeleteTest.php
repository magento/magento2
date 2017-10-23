<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Importer\Processor;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\Config\Importer\Processor\Delete;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteRepository;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for Delete processor.
 *
 * @see Delete
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Delete
     */
    private $model;

    /**
     * @var Registry|Mock
     */
    private $registryMock;

    /**
     * @var DataDifferenceCalculator|Mock
     */
    private $dataDifferenceCalculatorMock;

    /**
     * @var ManagerInterface|Mock
     */
    private $eventManagerMock;

    /**
     * @var WebsiteRepository|Mock
     */
    private $websiteRepositoryMock;

    /**
     * @var StoreRepository|Mock
     */
    private $storeRepositoryMock;

    /**
     * @var Collection|Mock
     */
    private $groupCollectionMock;

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
     * @var Store|Mock
     */
    private $storeMock;

    /**
     * @var StoreResource|Mock
     */
    private $storeResourceMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();
        $this->websiteRepositoryMock = $this->getMockBuilder(WebsiteRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteResourceMock = $this->getMockBuilder(WebsiteResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeRepositoryMock = $this->getMockBuilder(StoreRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupCollectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods(['getIterator', 'addFieldToFilter'])
            ->disableOriginalConstructor()
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

        $this->groupMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->groupResourceMock);
        $this->websiteMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->websiteResourceMock);
        $this->storeMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->storeResourceMock);

        $this->model = new Delete(
            $this->registryMock,
            $this->dataDifferenceCalculatorMock,
            $this->eventManagerMock,
            $this->websiteRepositoryMock,
            $this->storeRepositoryMock,
            $this->groupCollectionMock
        );
    }

    public function testRun()
    {
        $data = [
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_STORES => []
        ];
        $deleteData = [
            ScopeInterface::SCOPE_WEBSITES => [
                'test' => [
                    'website_id' => '2',
                    'code' => 'test',
                    'name' => 'Changed Main Test',
                    'sort_order' => '0',
                    'default_group_id' => '1',
                    'is_default' => '0',
                ]
            ],
            ScopeInterface::SCOPE_GROUPS => [
                2 => [
                    'group_id' => '2',
                    'website_id' => '2',
                    'name' => 'Changed Test Website Store',
                    'root_category_id' => '2',
                    'default_store_id' => '1',
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
                ],
            ],
        ];

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('isSecureArea')
            ->willReturn(false);
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('isSecureArea', true);
        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToDelete')
            ->willReturnMap([
                [
                    ScopeInterface::SCOPE_GROUPS,
                    $data[ScopeInterface::SCOPE_GROUPS],
                    $deleteData[ScopeInterface::SCOPE_GROUPS],
                ],
                [
                    ScopeInterface::SCOPE_WEBSITES,
                    $data[ScopeInterface::SCOPE_WEBSITES],
                    $deleteData[ScopeInterface::SCOPE_WEBSITES],
                ],
                [
                    ScopeInterface::SCOPE_STORES,
                    $data[ScopeInterface::SCOPE_STORES],
                    $deleteData[ScopeInterface::SCOPE_STORES],
                ],
            ]);

        $this->websiteRepositoryMock->expects($this->any())
            ->method('get')
            ->with('test')
            ->willReturn($this->websiteMock);
        $this->websiteResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->websiteMock);
        $this->groupCollectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('code', ['in' => [2]]);
        $this->groupCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->groupMock]));
        $this->groupResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->groupMock);
        $this->storeRepositoryMock->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn($this->storeMock);
        $this->storeResourceMock->expects($this->once())
            ->method('addCommitCallback');

        $this->registryMock->expects($this->once())
            ->method('unregister')
            ->with('isSecureArea');

        $this->model->run($data);
    }

    public function testRunNothingToDelete()
    {
        $data = [
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_STORES => []
        ];
        $deleteData = [
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_GROUPS => [],
            ScopeInterface::SCOPE_STORES => [],
        ];

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('isSecureArea')
            ->willReturn(false);
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('isSecureArea', true);
        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToDelete')
            ->willReturnMap([
                [
                    ScopeInterface::SCOPE_GROUPS,
                    $data[ScopeInterface::SCOPE_GROUPS],
                    $deleteData[ScopeInterface::SCOPE_GROUPS],
                ],
                [
                    ScopeInterface::SCOPE_WEBSITES,
                    $data[ScopeInterface::SCOPE_WEBSITES],
                    $deleteData[ScopeInterface::SCOPE_WEBSITES],
                ],
                [
                    ScopeInterface::SCOPE_STORES,
                    $data[ScopeInterface::SCOPE_STORES],
                    $deleteData[ScopeInterface::SCOPE_STORES],
                ],
            ]);

        $this->websiteResourceMock->expects($this->never())
            ->method('delete');
        $this->groupResourceMock->expects($this->never())
            ->method('delete');
        $this->storeMock->expects($this->never())
            ->method('delete');

        $this->registryMock->expects($this->once())
            ->method('unregister')
            ->with('isSecureArea');

        $this->model->run($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Some exception
     */
    public function testRunWithException()
    {
        $data = [
            ScopeInterface::SCOPE_WEBSITES => [],
            ScopeInterface::SCOPE_STORES => []
        ];

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('isSecureArea')
            ->willReturn(false);
        $this->registryMock->expects($this->once())
            ->method('register')
            ->with('isSecureArea', true);
        $this->dataDifferenceCalculatorMock->expects($this->once())
            ->method('getItemsToDelete')
            ->willThrowException(new \Exception('Some exception'));

        $this->websiteResourceMock->expects($this->never())
            ->method('delete');
        $this->groupResourceMock->expects($this->never())
            ->method('delete');
        $this->storeMock->expects($this->never())
            ->method('delete');

        $this->registryMock->expects($this->once())
            ->method('unregister')
            ->with('isSecureArea');

        $this->model->run($data);
    }
}
