<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Attribute;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface;
use Magento\Eav\Api\Data\AttributeGroupSearchResultsInterfaceFactory;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Attribute\GroupRepository;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupRepositoryTest extends TestCase
{
    /**
     * @var GroupRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $groupResourceMock;

    /**
     * @var MockObject
     */
    protected $groupFactoryMock;

    /**
     * @var MockObject
     */
    protected $setRepositoryMock;

    /**
     * @var MockObject
     */
    protected $searchResultsFactoryMock;

    /**
     * @var MockObject
     */
    protected $groupListFactoryMock;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->groupResourceMock = $this->createPartialMock(
            Group::class,
            ['delete', '__wakeup', 'load', 'save']
        );
        $this->groupFactoryMock = $this->createPartialMock(
            GroupFactory::class,
            ['create']
        );
        $this->setRepositoryMock = $this->getMockForAbstractClass(AttributeSetRepositoryInterface::class);
        $this->searchResultsFactoryMock = $this->createPartialMock(
            AttributeGroupSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->groupListFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            GroupRepository::class,
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
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testSaveIfObjectNew()
    {
        $attributeSetId = 42;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);

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
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testSaveIfObjectNotNew()
    {
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $existingGroupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);

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
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testSaveThrowExceptionIfAttributeSetDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with attributeSetId = -1');
        $attributeSetId = -1;
        $groupMock = $this->createPartialMock(\Magento\Eav\Model\Entity\Attribute\Group::class, ['getAttributeSetId']);
        $groupMock->expects($this->exactly(2))->method('getAttributeSetId')->willReturn($attributeSetId);
        $this->setRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeSetId)
            ->willThrowException(
                new NoSuchEntityException(__('AttributeSet does not exist.'))
            );
        $this->model->save($groupMock);
    }

    /**
     * Test saving throws exception if cannot save group
     *
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testSaveThrowExceptionIfCannotSaveGroup()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The attributeGroup can\'t be saved.');
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $existingGroupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
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
            ->willThrowException(new \Exception());
        $this->model->save($groupMock);
    }

    /**
     * Test saving throws exception if group does not belong to provided set
     *
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testSaveThrowExceptionIfGroupDoesNotBelongToProvidedSet()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The attribute group doesn\'t belong to the provided attribute set.');
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $existingGroupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
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
     * @throws NoSuchEntityException
     * @throws StateException
     * @return void
     */
    public function testSaveThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('No such entity with attributeGroupId =');
        $attributeSetId = 42;
        $groupId = 20;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $existingGroupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $attributeSetMock = $this->getMockForAbstractClass(AttributeSetInterface::class);
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
     * @throws InputException
     * @throws NoSuchEntityException
     * @return void
     */
    public function testGetList()
    {
        $filterInterfaceMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getField',
                'getValue',
            ])
            ->getMock();

        $filterGroupMock = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroupMock->expects($this->any())
            ->method('getFilters')
            ->willReturn([$filterInterfaceMock]);

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchCriteriaMock->expects($this->any())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);

        $groupMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Group::class)
            ->disableOriginalConstructor()
            ->getMock();

        $groupCollectionMock = $this->createPartialMock(
            AbstractCollection::class,
            ['getItems', 'getSize']
        );
        $groupCollectionMock->expects($this->once())->method('getItems')->willReturn([$groupMock]);

        $this->groupListFactoryMock->expects($this->once())->method('create')->willReturn($groupCollectionMock);

        $groupCollectionMock->expects($this->once())->method('getSize')->willReturn(1);

        $searchResultsMock = $this->getMockForAbstractClass(AttributeGroupSearchResultsInterface::class);
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
     * Test get
     *
     * @throws NoSuchEntityException
     * @return void
     */
    public function testGet()
    {
        $groupId = 42;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);
        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    /**
     * Test get throws exception if provided group does not exist
     *
     * @throws NoSuchEntityException
     * @return void
     */
    public function testGetThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The group with the "42" ID doesn\'t exist. Verify the ID and try again.');
        $groupId = 42;
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);
        $groupMock->expects($this->once())->method('getId')->willReturn(false);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    /**
     * Test delete
     *
     * @throws StateException
     * @return void
     */
    public function testDelete()
    {
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $this->groupResourceMock->expects($this->once())->method('delete')->with($groupMock);
        $this->assertTrue($this->model->delete($groupMock));
    }

    /**
     * Test deletion throws exception if provided group does not exist
     *
     * @throws StateException
     * @return void
     */
    public function testDeleteThrowExceptionIfProvidedGroupDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The attribute group with id "42" can\'t be deleted.');
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $this->groupResourceMock->expects($this->once())
            ->method('delete')
            ->with($groupMock)
            ->willThrowException(new \Exception());
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
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId);

        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $groupMock = $this->createMock(\Magento\Eav\Model\Entity\Attribute\Group::class);

        $this->groupResourceMock->expects($this->once())->method('delete')->with($groupMock);
        $this->assertTrue($this->model->deleteById($groupId));
    }
}
