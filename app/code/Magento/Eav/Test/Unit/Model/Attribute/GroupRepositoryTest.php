<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Attribute;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    protected $searchResultsFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupListFactoryMock;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->groupResourceMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group::class,
            ['delete', '__wakeup', 'load', 'save'],
            [],
            '',
            false
        );
        $this->groupFactoryMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\GroupFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->setRepositoryMock = $this->getMock(\Magento\Eav\Api\AttributeSetRepositoryInterface::class);
        $this->searchResultsFactoryMock = $this->getMock(
            \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->groupListFactoryMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Eav\Model\Attribute\GroupRepository::class,
            [
                'groupResource' => $this->groupResourceMock,
                'groupListFactory' => $this->groupListFactoryMock,
                'groupFactory' => $this->groupFactoryMock,
                'setRepository' => $this->setRepositoryMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );
    }

    /**
     * Test saving if object is new
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testSaveIfObjectNew()
    {
        $attributeSetId = 42;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);

        $groupMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);

        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willReturn($attributeSetMock);

        $this->groupResourceMock->expects($this->once())->method('save')->with($groupMock);
        $this->assertEquals($groupMock, $this->model->save($groupMock));
    }

    /**
     * Test saving if object is not new
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testSaveIfObjectNotNew()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $existingGroupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);

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
     * Test saving throws exception if attribute set does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = -1
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testSaveThrowExceptionIfAttributeSetDoesNotExist()
    {
        $attributeSetId = -1;
        $groupMock = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\Group::class,
            ['getAttributeSetId'],
            [],
            '',
            false
        );
        $groupMock->expects($this->exactly(2))->method('getAttributeSetId')->willReturn($attributeSetId);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->will(
                $this->throwException(
                    new \Magento\Framework\Exception\NoSuchEntityException(__('AttributeSet does not exist.'))
                )
            );
        $this->model->save($groupMock);
    }

    /**
     * Test saving throws exception if cannot save group
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot save attributeGroup
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testSaveThrowExceptionIfCannotSaveGroup()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $existingGroupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
        $groupMock->expects($this->any())->method('getAttributeSetId')->willReturn($attributeSetId);
        $groupMock->expects($this->any())->method('getAttributeGroupId')->willReturn($groupId);
        $attributeSetMock->expects($this->any())->method('getAttributeSetId')->willReturn(10);
        $this->setRepositoryMock->expects($this->once())->method('get')->with($attributeSetId)
            ->willReturn($attributeSetMock);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($existingGroupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($existingGroupMock, $groupId);
        $existingGroupMock->expects($this->any())->method('getId')->willReturn($groupId);
        $existingGroupMock->expects($this->once())->method('getAttributeSetId')->willReturn($attributeSetId);
        $this->groupResourceMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception()));
        $this->model->save($groupMock);
    }

    /**
     * Test saving throws exception if group does not belong to provided set
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Attribute group does not belong to provided attribute set
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testSaveThrowExceptionIfGroupDoesNotBelongToProvidedSet()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $existingGroupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
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
     * Test saving throws exception if provided group does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeGroupId =
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testSaveThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $existingGroupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $attributeSetMock = $this->getMock(\Magento\Eav\Api\Data\AttributeSetInterface::class);
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

    /**
     * Test get list
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetList()
    {
        $attributeSetId = 'filter';

        $filterInterfaceMock = $this->getMockBuilder(\Magento\Framework\Api\Search\FilterGroup::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getField',
                'getValue',
            ])
            ->getMock();
        $filterInterfaceMock->expects($this->once())
            ->method('getField')
            ->willReturn('attribute_set_id');
        $filterInterfaceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($attributeSetId);

        $filterGroupMock = $this->getMockBuilder(\Magento\Framework\Api\Search\FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterInterfaceMock]);

        $searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);

        $groupMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Group::class)
            ->disableOriginalConstructor()
            ->getMock();

        $groupCollectionMock = $this->getMock(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            ['getItems', 'getSize'],
            [],
            '',
            false
        );
        $groupCollectionMock->expects($this->once())->method('getItems')->willReturn([$groupMock]);

        $this->groupListFactoryMock->expects($this->once())->method('create')->willReturn($groupCollectionMock);

        $groupCollectionMock->expects($this->once())->method('getSize')->willReturn(1);

        $searchResultsMock = $this->getMock(
            \Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface::class,
            [],
            [],
            '',
            false
        );
        $searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $searchResultsMock->expects($this->once())->method('setItems')->with([$groupMock]);
        $searchResultsMock->expects($this->once())->method('setTotalCount')->with(1);
        $this->searchResultsFactoryMock->expects($this->once())->method('create')->willReturn($searchResultsMock);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $groupCollectionMock)
            ->willReturnSelf();

        $this->assertEquals($searchResultsMock, $this->model->getList($searchCriteriaMock));
    }

    /**
     * Test get list with invalid input exception
     *
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage attribute_set_id is a required field.
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetListWithInvalidInputException()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([]);
        $this->model->getList($searchCriteriaMock);
    }

    /**
     * Test get list with no such entity exception
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeSetId = filter
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetListWithNoSuchEntityException()
    {
        $attributeSetId = 'filter';
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $filterGroupMock = $this->getMock(\Magento\Framework\Api\Search\FilterGroup::class, [], [], '', false);
        $filterInterfaceMock = $this->getMock(\Magento\Framework\Api\Filter::class, [], [], '', false);

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

    /**
     * Test get
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGet()
    {
        $groupId = 42;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);
        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    /**
     * Test get throws exception if provided group does not exist
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Group with id "42" does not exist.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testGetThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $groupId = 42;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);
        $groupMock->expects($this->once())->method('getId')->willReturn(false);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    /**
     * Test delete
     *
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testDelete()
    {
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $this->groupResourceMock->expects($this->once())->method('delete')->with($groupMock);
        $this->assertTrue($this->model->delete($groupMock));
    }

    /**
     * Test deletion throws exception if provided group does not exist
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete attributeGroup with id
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testDeleteThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $this->groupResourceMock->expects($this->once())
            ->method('delete')
            ->with($groupMock)
            ->will($this->throwException(new \Exception()));
        $groupMock->expects($this->once())->method('getId')->willReturn(42);
        $this->model->delete($groupMock);
    }

    /**
     * Test delete by id
     *
     * @return void
     */
    public function testDeleteById()
    {
        $groupId = 42;
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);

        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $groupMock = $this->getMock(\Magento\Eav\Model\Entity\Attribute\Group::class, [], [], '', false);

        $this->groupResourceMock->expects($this->once())->method('delete')->with($groupMock);
        $this->assertTrue($this->model->deleteById($groupId));
    }
}
