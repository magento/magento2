<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for shipment repository class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    protected $subject;

    /**
     * Sales resource metadata.
     *
     * @var \Magento\Sales\Model\ResourceModel\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->metadata = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Metadata::class,
            ['getNewInstance', 'getMapper']
        );

        $this->searchResultFactory = $this->createPartialMock(
            \Magento\Sales\Api\Data\ShipmentSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $this->subject = $objectManager->getObject(
            \Magento\Sales\Model\Order\ShipmentRepository::class,
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );
    }

    /**
     * @param int|null $id
     * @param int|null $entityId
     * @dataProvider getDataProvider
     */
    public function testGet($id, $entityId)
    {
        if (!$id) {
            $this->expectException(\Magento\Framework\Exception\InputException::class);

            $this->subject->get($id);
        } else {
            $shipment = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['load', 'getEntityId']);
            $shipment->expects($this->once())
                ->method('load')
                ->with($id)
                ->willReturn($shipment);
            $shipment->expects($this->once())
                ->method('getEntityId')
                ->willReturn($entityId);

            $this->metadata->expects($this->once())
                ->method('getNewInstance')
                ->willReturn($shipment);

            if (!$entityId) {
                $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

                $this->subject->get($id);
            } else {
                $this->assertEquals($shipment, $this->subject->get($id));

                $shipment->expects($this->never())
                    ->method('load')
                    ->with($id)
                    ->willReturn($shipment);
                $shipment->expects($this->never())
                    ->method('getEntityId')
                    ->willReturn($entityId);

                $this->metadata->expects($this->never())
                    ->method('getNewInstance')
                    ->willReturn($shipment);

                // Retrieve Shipment from registry.
                $this->assertEquals($shipment, $this->subject->get($id));
            }
        }
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [null, null],
            [1, null],
            [1, 1]
        ];
    }

    public function testGetList()
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);

        $collection = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection::class);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->subject->getList($searchCriteria));
    }

    public function testDelete()
    {
        $shipment = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['getEntityId']);
        $shipment->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['delete']
        );
        $mapper->expects($this->once())
            ->method('delete')
            ->with($shipment);

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->subject->delete($shipment));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage The shipment couldn't be deleted.
     */
    public function testDeleteWithException()
    {
        $shipment = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['getEntityId']);
        $shipment->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['delete']
        );
        $mapper->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('error'));

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->subject->delete($shipment);
    }

    public function testSave()
    {
        $shipment = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['getEntityId']);
        $shipment->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );
        $mapper->expects($this->once())
            ->method('save')
            ->with($shipment);

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($shipment, $this->subject->save($shipment));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage The shipment couldn't be saved.
     */
    public function testSaveWithException()
    {
        $shipment = $this->createPartialMock(\Magento\Sales\Model\Order\Shipment::class, ['getEntityId']);
        $shipment->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );
        $mapper->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('error'));

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($shipment, $this->subject->save($shipment));
    }

    public function testCreate()
    {
        $shipment = $this->createMock(\Magento\Sales\Model\Order\Shipment::class);

        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($shipment);

        $this->assertEquals($shipment, $this->subject->create());
    }
}
