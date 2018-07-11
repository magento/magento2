<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\ShipmentCommentInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\Collection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ShipmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private $commentCollectionFactory;

    /**
     * @var Collection|MockObject
     */
    private $commentCollection;

    /**
     * @var \Magento\Sales\Model\Order\shipment
     */
    private $shipmentModel;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Sales\Api\ShipmentCommentRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentRepository;

    protected function setUp()
    {
        $helperManager = new ObjectManager($this);

        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->commentRepository = $this->getMockBuilder(\Magento\Sales\Api\ShipmentCommentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMockForAbstractClass();

        $this->initCommentsCollectionFactoryMock();

        $this->shipmentModel = $helperManager->getObject(Shipment::class, [
            'commentCollectionFactory' => $this->commentCollectionFactory,
            'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
            'shipmentCommentRepository' => $this->commentRepository,
        ]);
    }

    public function testGetIncrementId()
    {
        $this->shipmentModel->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->shipmentModel->getIncrementId());
    }

    /**
     * @covers \Magento\Sales\Model\Order\Shipment::getCommentsCollection
     */
    public function testGetCommentsCollection()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment'])
            ->getMock();
        $shipmentItem->expects(static::once())
            ->method('setShipment')
            ->with($this->shipmentModel);
        $collection = [$shipmentItem];

        $this->commentCollection->expects(static::once())
            ->method('setShipmentFilter')
            ->with($shipmentId)
            ->willReturnSelf();
        $this->commentCollection->expects(static::once())
            ->method('setCreatedAtOrder')
            ->willReturnSelf();

        $this->commentCollection->expects(static::once())
            ->method('load')
            ->willReturnSelf();

        $reflection = new \ReflectionClass(Collection::class);
        $reflectionProperty = $reflection->getProperty('_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->commentCollection, $collection);

        $expected = $this->shipmentModel->getCommentsCollection();

        static::assertEquals($expected, $this->commentCollection);
    }

    /**
     * @covers \Magento\Sales\Model\Order\Shipment::getComments
     */
    public function testGetComments()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $this->searchCriteriaBuilder->expects($this->any())->method('addFilter')
            ->willReturn($this->searchCriteriaBuilder);

        $commentSearchResult = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\ShipmentCommentSearchResultInterface::class,
            [],
            '',
            false
        );
        $this->commentRepository
            ->expects($this->any())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($commentSearchResult);

        $commentTest = $this->getMockBuilder(ShipmentCommentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $comments = [$commentTest];
        $commentSearchResult->expects($this->any())->method('getItems')->willReturn($comments);

        static::assertEquals($this->shipmentModel->getComments(), $comments);
    }

    /**
     * Creates mock for comments collection factory
     * @return void
     */
    private function initCommentsCollectionFactoryMock()
    {
        $this->commentCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipmentFilter', 'setCreatedAtOrder', 'getItems', 'load', '__wakeup'])
            ->getMock();

        $this->commentCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->commentCollectionFactory->expects(static::any())
            ->method('create')
            ->willReturn($this->commentCollection);
    }
}
