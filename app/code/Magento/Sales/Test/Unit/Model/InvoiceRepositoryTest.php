<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model;

/**
 * Class InvoiceRepositoryTest
 */
class InvoiceRepositoryTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->invoiceMetadata = $this->getMock('Magento\Sales\Model\ResourceModel\Metadata', [], [], '', false);
        $this->searchResultFactory = $this->getMockBuilder('Magento\Sales\Api\Data\InvoiceSearchResultInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->invoice = $objectManager->getObject(
            'Magento\Sales\Model\Order\InvoiceRepository',
            [
                'invoiceMetadata' => $this->invoiceMetadata,
                'searchResultFactory' => $this->searchResultFactory
            ]
        );
        $this->type = $this->getMock('Magento\Eav\Model\Entity\Type', ['fetchNewIncrementId'], [], '', false);
    }

    public function testGet()
    {
        $id = 1;

        $entity = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
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

        $entity = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
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
        $entity = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMetadata->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);
        $this->assertEquals($entity, $this->invoice->create());
    }

    public function testGetList()
    {
        $filterGroup = $this->getMockBuilder('Magento\Framework\Api\Search\FilterGroup')
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroups = [$filterGroup];
        $field = 'test_field';
        $fieldValue = 'test_value';

        $filter = $this->getMockBuilder('Magento\Framework\Api\Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->any())
            ->method('getConditionType')
            ->willReturn(false);
        $filter->expects($this->any())
            ->method('getField')
            ->willReturn($field);
        $filter->expects($this->any())
            ->method('getValue')
            ->willReturn($fieldValue);

        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filter]);

        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteria')
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn($filterGroups);

        $collection = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Invoice\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->withAnyParameters();

        $this->searchResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->invoice->getList($searchCriteria));
    }

    public function testDelete()
    {
        $entity = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Invoice')
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

        $entity = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
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

        $mapper = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Invoice')
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
        $entity = $this->getMockBuilder('Magento\Sales\Model\Order\Invoice')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder('Magento\Sales\Model\ResourceModel\Order\Invoice')
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
