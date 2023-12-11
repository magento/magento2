<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\ShipmentSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for shipment repository class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentRepositoryTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var ShipmentRepository
     */
    protected $subject;

    /**
     * Sales resource metadata.
     *
     * @var Metadata|MockObject
     */
    protected $metadata;

    /**
     * @var ShipmentSearchResultInterfaceFactory|MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->metadata = $this->createPartialMock(
            Metadata::class,
            ['getNewInstance', 'getMapper']
        );

        $this->searchResultFactory = $this->createPartialMock(
            ShipmentSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->subject = $objectManager->getObject(
            ShipmentRepository::class,
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
            $this->expectException(InputException::class);

            $this->subject->get($id);
        } else {
            $shipment = $this->createPartialMock(Shipment::class, ['load', 'getEntityId']);
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
                $this->expectException(NoSuchEntityException::class);

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
        $searchCriteria = $this->createMock(SearchCriteria::class);

        $collection = $this->createMock(Collection::class);
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
        $shipment = $this->createPartialMock(Shipment::class, ['getEntityId']);
        $shipment->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
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

    public function testDeleteWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('The shipment couldn\'t be deleted.');
        $shipment = $this->createPartialMock(Shipment::class, ['getEntityId']);
        $shipment->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
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
        $shipment = $this->createPartialMock(Shipment::class, ['getEntityId']);
        $shipment->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
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

    public function testSaveWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The shipment couldn\'t be saved.');
        $shipment = $this->createPartialMock(Shipment::class, ['getEntityId']);
        $shipment->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
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

    public function testSaveWithValidatorException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $shipment = $this->createPartialMock(Shipment::class, ['getEntityId']);
        $shipment->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockForAbstractClass(
            AbstractDb::class,
            [],
            '',
            false,
            true,
            true,
            ['save']
        );
        $mapper->expects($this->once())
            ->method('save')
            ->willThrowException(new \Magento\Framework\Validator\Exception());

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($shipment, $this->subject->save($shipment));
    }

    public function testCreate()
    {
        $shipment = $this->createMock(Shipment::class);

        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($shipment);

        $this->assertEquals($shipment, $this->subject->create());
    }
}
