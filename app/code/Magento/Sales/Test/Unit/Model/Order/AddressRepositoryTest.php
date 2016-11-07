<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for order address repository class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Sales\Model\Order\AddressRepository
     */
    protected $subject;

    /**
     * Sales resource metadata.
     *
     * @var \Magento\Sales\Model\ResourceModel\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    /**
     * @var \Magento\Sales\Api\Data\OrderAddressSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var CollectionProcessorInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->metadata = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Metadata::class,
            ['getNewInstance', 'getMapper'],
            [],
            '',
            false
        );

        $this->searchResultFactory = $this->getMock(
            \Magento\Sales\Api\Data\OrderAddressSearchResultInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->subject = $objectManager->getObject(
            \Magento\Sales\Model\Order\AddressRepository::class,
            [
                'metadata' => $this->metadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessorMock,
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
            $this->setExpectedException(
                \Magento\Framework\Exception\InputException::class
            );

            $this->subject->get($id);
        } else {
            $address = $this->getMock(
                \Magento\Sales\Model\Order\Address::class,
                ['load', 'getEntityId'],
                [],
                '',
                false
            );
            $address->expects($this->once())
                ->method('load')
                ->with($id)
                ->willReturn($address);
            $address->expects($this->once())
                ->method('getEntityId')
                ->willReturn($entityId);

            $this->metadata->expects($this->once())
                ->method('getNewInstance')
                ->willReturn($address);

            if (!$entityId) {
                $this->setExpectedException(
                    \Magento\Framework\Exception\NoSuchEntityException::class
                );

                $this->subject->get($id);
            } else {
                $this->assertEquals($address, $this->subject->get($id));

                $address->expects($this->never())
                    ->method('load')
                    ->with($id)
                    ->willReturn($address);
                $address->expects($this->never())
                    ->method('getEntityId')
                    ->willReturn($entityId);

                $this->metadata->expects($this->never())
                    ->method('getNewInstance')
                    ->willReturn($address);

                // Retrieve Address from registry.
                $this->assertEquals($address, $this->subject->get($id));
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
        $searchCriteria = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
        $collection = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Address\Collection::class,
            [],
            [],
            '',
            false
        );

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->subject->getList($searchCriteria));
    }

    public function testDelete()
    {
        $address = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getEntityId'],
            [],
            '',
            false
        );
        $address->expects($this->once())
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
            ->with($address);

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->subject->delete($address));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Could not delete order address
     */
    public function testDeleteWithException()
    {
        $address = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getEntityId'],
            [],
            '',
            false
        );
        $address->expects($this->never())
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

        $this->subject->delete($address);
    }

    public function testSave()
    {
        $address = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getEntityId'],
            [],
            '',
            false
        );
        $address->expects($this->any())
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
            ->with($address);

        $this->metadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($address, $this->subject->save($address));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save order address
     */
    public function testSaveWithException()
    {
        $address = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getEntityId'],
            [],
            '',
            false
        );
        $address->expects($this->never())
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

        $this->assertEquals($address, $this->subject->save($address));
    }

    public function testCreate()
    {
        $address = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            ['getEntityId'],
            [],
            '',
            false
        );

        $this->metadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($address);

        $this->assertEquals($address, $this->subject->create());
    }
}
