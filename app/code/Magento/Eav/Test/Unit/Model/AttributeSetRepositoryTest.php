<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\AttributeSetRepository;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
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
     * @return void
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMock(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Set',
            [],
            [],
            '',
            false
        );
        $this->setFactoryMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\SetFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->collectionFactoryMock = $this->getMock(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', ['getEntityType'], [], '', false);
        $this->resultFactoryMock = $this->getMock(
            '\Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            '\Magento\Framework\Api\ExtensionAttribute\JoinProcessor',
            ['process'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Eav\Model\AttributeSetRepository(
            $this->resourceMock,
            $this->setFactoryMock,
            $this->collectionFactoryMock,
            $this->eavConfigMock,
            $this->resultFactoryMock,
            $this->extensionAttributesJoinProcessorMock
        );
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $attributeSetId = 1;
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
        $this->setFactoryMock->expects($this->once())->method('create')->will($this->returnValue($attributeSetMock));
        $this->resourceMock->expects($this->once())->method('load')->with($attributeSetMock, $attributeSetId, null);
        $this->model->get($attributeSetId);
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $attributeSetMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
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
        $entityTypeCode = 'entity_type_code_value';
        $entityTypeId = 41;

        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');

        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $searchCriteriaMock->expects($this->exactly(2))->method('getFilterGroups')->willReturn([$filterGroupMock]);

        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $filterGroupMock->expects($this->exactly(2))->method('getFilters')->willReturn([$filterMock]);

        $filterMock->expects($this->exactly(2))->method('getField')->willReturn('entity_type_code');
        $filterMock->expects($this->once())->method('getValue')->willReturn($entityTypeCode);

        $collectionMock = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection',
            ['setEntityTypeFilter', 'setCurPage', 'setPageSize', 'getItems', 'getSize'],
            [],
            '',
            false
        );

        $entityTypeMock = $this->getMock('\Magento\Eav\Model\Entity\Type', [], [], '', false);
        $entityTypeMock->expects($this->once())->method('getId')->willReturn($entityTypeId);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityTypeCode)
            ->willReturn($entityTypeMock);

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('setEntityTypeFilter')
            ->with($entityTypeId)
            ->willReturnSelf();

        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(1);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(10);

        $collectionMock->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setPageSize')->with(10)->willReturnSelf();

        $attributeSetMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$attributeSetMock]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(1);

        $resultMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetSearchResultsInterface', [], [], '', false);
        $resultMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setItems')
            ->with([$attributeSetMock])
            ->willReturnSelf();
        $resultMock->expects($this->once())->method('setTotalCount')->with(1)->willReturnSelf();

        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($resultMock);

        $this->model->getList($searchCriteriaMock);
    }

    /**
     * @return void
     */
    public function testGetListIfEntityTypeCodeIsNull()
    {
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $searchCriteriaMock->expects($this->exactly(2))->method('getFilterGroups')->willReturn([]);

        $collectionMock = $this->getMock(
            '\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection',
            ['setCurPage', 'setPageSize', 'getItems', 'getSize'],
            [],
            '',
            false
        );

        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);

        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn(1);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn(10);

        $collectionMock->expects($this->once())->method('setCurPage')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('setPageSize')->with(10)->willReturnSelf();

        $attributeSetMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Set', [], [], '', false);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$attributeSetMock]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn(1);

        $resultMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetSearchResultsInterface', [], [], '', false);
        $resultMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $resultMock->expects($this->once())
            ->method('setItems')
            ->with([$attributeSetMock])
            ->willReturnSelf();
        $resultMock->expects($this->once())->method('setTotalCount')->with(1)->willReturnSelf();

        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($resultMock);

        $this->model->getList($searchCriteriaMock);
    }
}
