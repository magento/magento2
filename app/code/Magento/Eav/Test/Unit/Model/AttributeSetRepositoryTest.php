<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\AttributeSetRepository;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeSetRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeSetRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $setFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class,
            [],
            [],
            '',
            false
        );
        $this->setFactoryMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\SetFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->eavConfigMock = $this->getMock(\Magento\Eav\Model\Config::class, ['getEntityType'], [], '', false);
        $this->resultFactoryMock = $this->getMock(
            \Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class,
            ['process'],
            [],
            '',
            false
        );

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->model = new \Magento\Eav\Model\AttributeSetRepository(
            $this->resourceMock,
            $this->setFactoryMock,
            $this->collectionFactoryMock,
            $this->eavConfigMock,
            $this->resultFactoryMock,
            $this->extensionAttributesJoinProcessorMock,
            $this->collectionProcessor
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $this->resourceMock->expects($this->once())->method('load')->with($attributeSetMock, $attributeSetId, null);
        $attributeSetMock->expects($this->any())->method('getId')->will($this->returnValue($attributeSetId));
        $this->assertEquals($attributeSetMock, $this->model->get($attributeSetId));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 9999
     */
    public function testGetThrowsExceptionIfRequestedAttributeSetDoesNotExist()
    {
        $attributeSetId = 9999;
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $this->resourceMock->expects($this->once())->method('load')->with($attributeSetMock, $attributeSetId, null);
        $this->model->get($attributeSetId);
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->resourceMock->expects($this->once())->method('save')->with($attributeSetMock);
        $this->assertEquals($attributeSetMock, $this->model->save($attributeSetMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage There was an error saving attribute set.
     */
    public function testSaveThrowsExceptionIfGivenEntityCannotBeSaved()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->resourceMock->expects($this->once())->method('save')->with($attributeSetMock)->willThrowException(
            new \Exception('Some internal exception message.')
        );
        $this->model->save($attributeSetMock);
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->resourceMock->expects($this->once())->method('delete')->with($attributeSetMock);
        $this->assertTrue($this->model->delete($attributeSetMock));
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage There was an error deleting attribute set.
     */
    public function testDeleteThrowsExceptionIfGivenEntityCannotBeDeleted()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->resourceMock->expects($this->once())->method('delete')->with($attributeSetMock)->willThrowException(
            new \Magento\Framework\Exception\CouldNotDeleteException(__('Some internal exception message.'))
        );
        $this->model->delete($attributeSetMock);
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Default attribute set can not be deleted
     */
    public function testDeleteThrowsExceptionIfGivenAttributeSetIsDefault()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $this->resourceMock->expects($this->once())->method('delete')->with($attributeSetMock)->willThrowException(
            new \Magento\Framework\Exception\StateException(__('Some internal exception message.'))
        );
        $this->model->delete($attributeSetMock);
    }

    /**
     * @return void
     */
    public function testDeleteById()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);
        $attributeSetMock->expects($this->any())->method('getId')->will($this->returnValue($attributeSetId));
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $this->resourceMock->expects($this->once())->method('load')->with($attributeSetMock, $attributeSetId, null);
        $this->resourceMock->expects($this->once())->method('delete')->with($attributeSetMock);
        $this->assertTrue($this->model->deleteById($attributeSetId));
    }

    /**
     * @return void
     */
    public function testGetList()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);

        $collectionMock = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getItems',
                'getSize',
            ])
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attributeSetMock]);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);

        $resultMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeSetSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $resultMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setItems')
            ->with([$attributeSetMock])
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setTotalCount')
            ->with(1)
            ->willReturnSelf();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock)
            ->willReturnSelf();

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * @return void
     */
    public function testGetListIfEntityTypeCodeIsNull()
    {
        $attributeSetMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Set::class, [], [], '', false);

        $collectionMock = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getItems',
                'getSize',
            ])
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attributeSetMock]);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn(1);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);

        $resultMock = $this->getMockBuilder(\Magento\Eav\Api\Data\AttributeSetSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $resultMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setItems')
            ->with([$attributeSetMock])
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setTotalCount')
            ->with(1)
            ->willReturnSelf();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultMock);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock)
            ->willReturnSelf();

        $this->model->getList($searchCriteriaMock);
    }
}
