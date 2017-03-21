<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SortOrder;

/**
 * Class OrderRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $model;

    /**
     * @var Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var SearchResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $className = \Magento\Sales\Model\ResourceModel\Metadata::class;
        $this->metadata = $this->getMock($className, [], [], '', false);

        $className = \Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory::class;
        $this->searchResultFactory = $this->getMock($className, ['create'], [], '', false);
        $this->collectionProcessor = $this->getMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->model = $this->objectManager->getObject(
            \Magento\Sales\Model\OrderRepository::class,
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );
    }

    /**
     * TODO: Cover with unit tests the other methods in the repository
     * test GetList
     */
    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $collectionMock = $this->getMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class, [], [], '', false);
        $itemsMock = $this->getMockBuilder(OrderInterface::class)->disableOriginalConstructor()->getMock();

        $extensionAttributes = $this->getMock(
            \Magento\Sales\Api\Data\OrderExtension::class,
            ['getShippingAssignments'],
            [],
            '',
            false
        );
        $shippingAssignmentBuilder = $this->getMock(
            \Magento\Sales\Model\Order\ShippingAssignmentBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $itemsMock->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->any())
            ->method('getShippingAssignments')
            ->willReturn($shippingAssignmentBuilder);

        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$itemsMock]);

        $this->assertEquals($collectionMock, $this->model->getList($searchCriteriaMock));
    }

    public function testSave()
    {
        $mapperMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderEntity = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
        $extensionAttributes = $this->getMock(
            \Magento\Sales\Api\Data\OrderExtension::class,
            ['getShippingAssignments'],
            [],
            '',
            false
        );
        $shippingAssignment = $this->getMockBuilder(\Magento\Sales\Model\Order\ShippingAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShipping'])
            ->getMock();
        $shippingMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipping::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddress', 'getMethod'])
            ->getMock();
        $orderEntity->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $orderEntity->expects($this->once())->method('getIsNotVirtual')->willReturn(true);
        $extensionAttributes
            ->expects($this->any())
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignment]);
        $shippingAssignment->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress');
        $shippingMock->expects($this->once())->method('getMethod');
        $this->metadata->expects($this->once())->method('getMapper')->willReturn($mapperMock);
        $mapperMock->expects($this->once())->method('save');
        $orderEntity->expects($this->any())->method('getEntityId')->willReturn(1);
        $this->model->save($orderEntity);
    }
}
