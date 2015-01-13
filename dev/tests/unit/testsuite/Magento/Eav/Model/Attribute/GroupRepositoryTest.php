<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Attribute;

class GroupRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\GroupRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupListFactoryMock;

    protected function setUp()
    {
        $this->groupResourceMock = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Group',
            ['delete', '__wakeup', 'load', 'save'],
            [],
            '',
            false
        );
        $this->groupFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Attribute\GroupFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->setRepositoryMock = $this->getMock('\Magento\Eav\Api\AttributeSetRepositoryInterface');
        $this->searchResultsBuilderMock = $this->getMock(
            '\Magento\Eav\Api\Data\AttributeGroupSearchResultsDataBuilder',
            ['setSearchCriteria', 'setItems', 'setTotalCount', 'create'],
            [],
            '',
            false
        );
        $this->groupListFactoryMock = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Eav\Model\Attribute\GroupRepository',
            [
                'groupResource' => $this->groupResourceMock,
                'groupListFactory' => $this->groupListFactoryMock,
                'groupFactory' => $this->groupFactoryMock,
                'setRepository' => $this->setRepositoryMock,
                'searchResultsBuilder' => $this->searchResultsBuilderMock
            ]
        );
    }

    public function testSaveIfObjectNew()
    {
        $attributeSetId = 42;
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $attributeSetMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetInterface');

        $groupMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);

        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);

        $this->groupResourceMock->expects($this->once())->method('save')->with($groupMock);
        $this->assertEquals($groupMock, $this->model->save($groupMock));
    }

    public function testSaveIfObjectNotNew()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $existingGroupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $attributeSetMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetInterface');

        $groupMock->expects($this->exactly(2))->method('getAttributeSetId')->willReturn($attributeSetId);
        $groupMock->expects($this->exactly(2))->method('getAttributeGroupId')->willReturn($groupId);

        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($existingGroupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($existingGroupMock, $groupId);

        $existingGroupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $existingGroupMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);

        $this->groupResourceMock->expects($this->once())->method('save')->with($groupMock);
        $this->assertEquals($groupMock, $this->model->save($groupMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = -1
     */
    public function testSaveThrowExceptionIfAttributeSetDoesNotExist()
    {
        $attributeSetId = -1;
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', ['getAttributeSetId'], [], '', false);
        $groupMock->expects($this->exactly(2))->method('getAttributeSetId')->willReturn($attributeSetId);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->will(
                $this->throwException(
                    new \Magento\Framework\Exception\NoSuchEntityException('AttributeSet does not exist.')
                )
            );
        $this->model->save($groupMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot save attributeGroup
     */
    public function testSaveThrowExceptionIfCannotSaveGroup()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock('\Magento\Eav\Api\Data\AttributeGroupInterface');
        $existingGroupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $attributeSetMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetInterface');
        $groupMock->expects($this->any())->method('getAttributeSetId')->willReturn($attributeSetId);
        $groupMock->expects($this->any())->method('getAttributeGroupId')->willReturn($groupId);
        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())->method('get')->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($existingGroupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($existingGroupMock, $groupId);
        $existingGroupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $existingGroupMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);
        $this->model->save($groupMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Attribute group does not belong to provided attribute set
     */
    public function testSaveThrowExceptionIfGroupDoesNotBelongToProvidedSet()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock('\Magento\Eav\Api\Data\AttributeGroupInterface');
        $existingGroupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $attributeSetMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetInterface');
        $groupMock->expects($this->any())->method('getAttributeSetId')->willReturn($attributeSetId);
        $groupMock->expects($this->any())->method('getAttributeGroupId')->willReturn($groupId);
        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())->method('get')->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($existingGroupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($existingGroupMock, $groupId);
        $existingGroupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $this->model->save($groupMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeGroupId =
     */
    public function testSaveThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock('\Magento\Eav\Api\Data\AttributeGroupInterface');
        $existingGroupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $attributeSetMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetInterface');
        $groupMock->expects($this->any())->method('getAttributeSetId')->willReturn($attributeSetId);
        $groupMock->expects($this->any())->method('getAttributeGroupId')->willReturn($groupId);
        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())->method('get')->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($existingGroupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($existingGroupMock, $groupId);
        $existingGroupMock->expects($this->any())->method('getId')->willReturn(false);
        $this->model->save($groupMock);
    }

    public function testGetList()
    {
        $attributeSetId = 'filter';
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterInterfaceMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $attributeSetMock = $this->getMock('\Magento\Eav\Api\Data\AttributeSetInterface');
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);

        $groupCollectionMock = $this->getMock(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection',
            ['setAttributeSetFilter', 'setSortOrder', 'getItems', 'getSize'],
            [],
            '',
            false
        );
        $groupCollectionMock->expects($this->once())->method('getItems')->willReturn([$groupMock]);
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);

        $filterGroupMock->expects($this->once())->method('getFilters')->willReturn([$filterInterfaceMock]);
        $filterInterfaceMock->expects($this->once())->method('getField')->willReturn('attribute_set_id');
        $filterInterfaceMock->expects($this->once())->method('getValue')->willReturn($attributeSetId);

        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->groupListFactoryMock->expects($this->once())->method('create')->willReturn($groupCollectionMock);

        $groupCollectionMock->expects($this->once())->method('setAttributeSetFilter')->with($attributeSetId);
        $groupCollectionMock->expects($this->once())->method('setSortOrder');
        $groupCollectionMock->expects($this->once())->method('getSize')->willReturn(1);

        $this->searchResultsBuilderMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->searchResultsBuilderMock->expects($this->once())->method('setItems')->with([$groupMock]);
        $this->searchResultsBuilderMock->expects($this->once())->method('setTotalCount')->with(1);
        $this->searchResultsBuilderMock->expects($this->once())->method('create')->willReturnSelf();
        $this->assertEquals($this->searchResultsBuilderMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage attribute_set_id is a required field.
     */
    public function testGetListWithInvalidInputException()
    {
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([]);
        $this->model->getList($searchCriteriaMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = filter
     */
    public function testGetListWithNoSuchEntityException()
    {
        $attributeSetId = 'filter';
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterInterfaceMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);

        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);

        $filterGroupMock->expects($this->once())->method('getFilters')->willReturn([$filterInterfaceMock]);
        $filterInterfaceMock->expects($this->once())->method('getField')->willReturn('attribute_set_id');
        $filterInterfaceMock->expects($this->once())->method('getValue')->willReturn($attributeSetId);

        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([]);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willThrowException(new \Exception());
        $this->model->getList($searchCriteriaMock);
    }

    public function testGet()
    {
        $groupId = 42;
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);
        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Group with id "42" does not exist.
     */
    public function testGetThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $groupId = 42;
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);
        $groupMock->expects($this->once())->method('getId')->willReturn(false);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    public function testDelete()
    {
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $this->groupResourceMock->expects($this->once())->method('delete')->with($groupMock);
        $this->assertTrue($this->model->delete($groupMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete attributeGroup with id
     */
    public function testDeleteThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $this->groupResourceMock->expects($this->once())
            ->method('delete')
            ->with($groupMock)
            ->will($this->throwException(new \Exception()));
        $groupMock->expects($this->once())->method('getId')->willReturn(42);
        $this->model->delete($groupMock);
    }

    public function testDeleteById()
    {
        $groupId = 42;
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);

        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $groupMock = $this->getMock('\Magento\Eav\Model\Entity\Attribute\Group', [], [], '', false);

        $this->groupResourceMock->expects($this->once())->method('delete')->with($groupMock);
        $this->assertTrue($this->model->deleteById($groupId));
    }
}
