<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Sales\Model\Order\CreditmemoRepository;

/**
 * Class CreditmemoRepositoryTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreditmemoRepository
     */
    protected $creditmemo;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Metadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactoryMock;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->metadataMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Metadata::class);
        $this->searchResultFactoryMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $this->creditmemo = $objectManager->getObject(
            \Magento\Sales\Model\Order\CreditmemoRepository::class,
            [
                'metadata' => $this->metadataMock,
                'searchResultFactory' => $this->searchResultFactoryMock,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
        $this->type = $this->createPartialMock(\Magento\Eav\Model\Entity\Type::class, ['fetchNewIncrementId']);
    }

    public function testGet()
    {
        $id = 1;
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn($id);

        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $this->assertEquals($entity, $this->creditmemo->get($id));
        //retrieve entity from registry
        $this->assertEquals($entity, $this->creditmemo->get($id));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage An ID is needed. Set the ID and try again.
     */
    public function testGetNoId()
    {
        $this->creditmemo->get(null);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage The entity that was requested doesn't exist. Verify the entity and try again.
     */
    public function testGetEntityNoId()
    {
        $id = 1;
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturn($entity);
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(null);

        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);

        $this->assertNull($entity, $this->creditmemo->get($id));
    }

    public function testCreate()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);
        $this->assertEquals($entity, $this->creditmemo->create());
    }

    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $this->searchResultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($collection, $this->creditmemo->getList($searchCriteria));
    }

    public function testDelete()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('delete')
            ->with($entity);

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertTrue($this->creditmemo->delete($entity));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage The credit memo couldn't be deleted.
     */
    public function testDeleteWithException()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('error'));

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->creditmemo->delete($entity);
    }

    public function testSave()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('save')
            ->with($entity);

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($entity, $this->creditmemo->save($entity));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage The credit memo couldn't be saved.
     */
    public function testSaveWithException()
    {
        $entity = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->never())
            ->method('getEntityId');

        $mapper = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mapper->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('error'));

        $this->metadataMock->expects($this->any())
            ->method('getMapper')
            ->willReturn($mapper);

        $this->assertEquals($entity, $this->creditmemo->save($entity));
    }
}
