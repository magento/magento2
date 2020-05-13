<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoRepositoryTest extends TestCase
{
    /**
     * @var CreditmemoRepository
     */
    protected $creditmemo;

    /**
     * @var Metadata|MockObject
     */
    protected $metadataMock;

    /**
     * @var CreditmemoSearchResultInterfaceFactory|MockObject
     */
    protected $searchResultFactoryMock;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->metadataMock = $this->createMock(Metadata::class);
        $this->searchResultFactoryMock = $this->createPartialMock(
            CreditmemoSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $this->creditmemo = $objectManager->getObject(
            CreditmemoRepository::class,
            [
                'metadata' => $this->metadataMock,
                'searchResultFactory' => $this->searchResultFactoryMock,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
        $this->type = $this->createPartialMock(Type::class, ['fetchNewIncrementId']);
    }

    public function testGet()
    {
        $id = 1;
        $entity = $this->getMockBuilder(Creditmemo::class)
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

    public function testGetNoId()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('An ID is needed. Set the ID and try again.');
        $this->creditmemo->get(null);
    }

    public function testGetEntityNoId()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The entity that was requested doesn\'t exist. Verify the entity and try again.');
        $id = 1;
        $entity = $this->getMockBuilder(Creditmemo::class)
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
        $entity = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock->expects($this->once())
            ->method('getNewInstance')
            ->willReturn($entity);
        $this->assertEquals($entity, $this->creditmemo->create());
    }

    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(Collection::class)
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
        $entity = $this->getMockBuilder(Creditmemo::class)
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

    public function testDeleteWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('The credit memo couldn\'t be deleted.');
        $entity = $this->getMockBuilder(Creditmemo::class)
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
        $entity = $this->getMockBuilder(Creditmemo::class)
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

    public function testSaveWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The credit memo couldn\'t be saved.');
        $entity = $this->getMockBuilder(Creditmemo::class)
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
