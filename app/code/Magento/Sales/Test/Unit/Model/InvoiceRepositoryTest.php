<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Class InvoiceRepositoryTest
 */
class InvoiceRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\InvoiceRepository
     */
    protected $invoice;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var CollectionProcessorInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;
    
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->invoiceMetadata = $this->createMock(\Magento\Sales\Model\ResourceModel\Metadata::class);
        $this->searchResultFactory = $this->getMockBuilder(
            \Magento\Sales\Api\Data\InvoiceSearchResultInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $this->invoice = $objectManager->getObject(
            \Magento\Sales\Model\Order\InvoiceRepository::class,
            [
                'invoiceMetadata' => $this->invoiceMetadata,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
        $this->type = $this->createPartialMock(\Magento\Eav\Model\Entity\Type::class, ['fetchNewIncrementId']);
    }

    public function testGet()
    {
        $id = 1;

        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);

        $this->invoiceMetadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $this->assertEquals($entity, $this->invoice->get($id));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage ID required
     */
    public function testGetNoId()
    {
        $this->invoice->get(null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested entity doesn't exist
     */
    public function testGetEntityNoId()
    {
        $id = 1;

        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(null);

        $this->invoiceMetadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $this->assertNull($entity, $this->invoice->get($id));
    }

    public function testCreate()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMetadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);
        $this->assertEquals($entity, $this->invoice->create());
    }

    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->invoice->getList($searchCriteria));
    }

    public function testDelete()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('delete')
            ->with($entity);

        $this->invoiceMetadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->invoice->delete($entity));
    }

    public function testDeleteById()
    {
        $id = 1;

        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->any())
            ->method('getEntityId')
            ->willReturn($id);

        $this->invoiceMetadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('delete')
            ->with($entity);

        $this->invoiceMetadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->invoice->deleteById($id));
    }

    public function testSave()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('save')
            ->with($entity);

        $this->invoiceMetadata->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($entity, $this->invoice->save($entity));
    }
}
