<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\GroupExtensionInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterfaceFactory;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupRepositoryTest extends TestCase
{
    /**
     * @var GroupRegistry|MockObject
     */
    protected $groupRegistry;

    /**
     * @var GroupFactory|MockObject
     */
    protected $groupFactory;

    /**
     * @var \Magento\Customer\Model\Group|MockObject
     */
    protected $groupModel;

    /**
     * @var GroupInterfaceFactory|MockObject
     */
    protected $groupDataFactory;

    /**
     * @var GroupInterface|MockObject
     */
    protected $group;

    /**
     * @var GroupInterface|MockObject
     */
    protected $factoryCreatedGroup;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group|MockObject
     */
    protected $groupResourceModel;

    /**
     * @var DataObjectProcessor|MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var GroupSearchResultsInterfaceFactory|MockObject
     */
    protected $searchResultsFactory;

    /**
     * @var GroupSearchResultsInterface|MockObject
     */
    protected $searchResults;

    /**
     * @var TaxClassRepositoryInterface|MockObject
     */
    private $taxClassRepository;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var GroupRepository
     */
    protected $model;

    protected function setUp(): void
    {
        $this->setupGroupObjects();
        $this->dataObjectProcessor = $this->createMock(DataObjectProcessor::class);
        $this->searchResultsFactory = $this->createPartialMock(
            GroupSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->searchResults = $this->getMockForAbstractClass(
            GroupSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->taxClassRepository = $this->getMockForAbstractClass(
            TaxClassRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $this->model = new GroupRepository(
            $this->groupRegistry,
            $this->groupFactory,
            $this->groupDataFactory,
            $this->groupResourceModel,
            $this->dataObjectProcessor,
            $this->searchResultsFactory,
            $this->taxClassRepository,
            $this->extensionAttributesJoinProcessor,
            $this->collectionProcessorMock
        );
    }

    private function setupGroupObjects()
    {
        $this->groupRegistry = $this->createMock(GroupRegistry::class);
        $this->groupFactory = $this->createPartialMock(GroupFactory::class, ['create']);
        $this->groupModel = $this->getMockBuilder(Group::class)
            ->addMethods(['getTaxClassId', 'setTaxClassId'])
            ->onlyMethods(
                [
                    'getTaxClassName',
                    'getId',
                    'getCode',
                    'setDataUsingMethod',
                    'setCode',
                    'usesAsDefault',
                    'delete',
                    'getCollection',
                    'getData',
                ]
            )
            ->setMockClassName('groupModel')
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupDataFactory = $this->createPartialMock(
            GroupInterfaceFactory::class,
            ['create']
        );
        $this->group = $this->getMockForAbstractClass(
            GroupInterface::class,
            [],
            'group',
            false
        );
        $this->factoryCreatedGroup = $this->getMockForAbstractClass(
            GroupInterface::class,
            [],
            'group',
            false
        );

        $this->groupResourceModel = $this->createMock(\Magento\Customer\Model\ResourceModel\Group::class);
    }

    public function testSave()
    {
        $groupId = 0;

        $taxClass = $this->getMockForAbstractClass(TaxClassInterface::class, [], '', false);
        $extensionAttributes = $this->getMockForAbstractClass(
            GroupExtensionInterface::class
        );

        $this->group->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->group->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->group->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(17);
        $this->group->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->groupModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassName')
            ->willReturn('Tax class name');

        $this->factoryCreatedGroup->expects($this->once())
            ->method('setId')
            ->with($groupId)
            ->willReturnSelf();
        $this->factoryCreatedGroup->expects($this->once())
            ->method('setCode')
            ->with('Code')
            ->willReturnSelf();
        $this->factoryCreatedGroup->expects($this->once())
            ->method('setTaxClassId')
            ->with(234)
            ->willReturnSelf();
        $this->factoryCreatedGroup->expects($this->once())
            ->method('setTaxClassName')
            ->with('Tax class name')
            ->willReturnSelf();
        $this->factoryCreatedGroup->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes)
            ->willReturnSelf();
        $this->factoryCreatedGroup->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->factoryCreatedGroup->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(17);

        $this->taxClassRepository->expects($this->once())
            ->method('get')
            ->with(17)
            ->willReturn($taxClass);
        $taxClass->expects($this->once())
            ->method('getClassType')
            ->willReturn('CUSTOMER');

        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);
        $this->dataObjectProcessor->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($this->group, GroupInterface::class)
            ->willReturn(['attributeCode' => 'attributeData']);
        $this->groupModel->expects($this->once())
            ->method('setDataUsingMethod')
            ->with('attributeCode', 'attributeData');
        $this->groupResourceModel->expects($this->once())
            ->method('save')
            ->with($this->groupModel);
        $this->groupRegistry->expects($this->once())
            ->method('remove')
            ->with($groupId);
        $this->groupDataFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->factoryCreatedGroup);

        $updatedGroup = $this->model->save($this->group);

        $this->assertSame($this->group->getCode(), $updatedGroup->getCode());
        $this->assertSame($this->group->getTaxClassId(), $updatedGroup->getTaxClassId());
    }

    public function testSaveWithException()
    {
        $this->expectException(InvalidTransitionException::class);

        $taxClass = $this->getMockForAbstractClass(TaxClassInterface::class, [], '', false);

        $this->groupFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->groupModel);

        $this->group->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->group->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(false);
        $this->group->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->group->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(17);

        $this->groupModel->expects($this->once())
            ->method('setCode')
            ->with('Code');
        $this->groupModel->expects($this->once())
            ->method('setTaxClassId')
            ->with(234);

        $this->taxClassRepository->expects($this->once())
            ->method('get')
            ->with(234)
            ->willReturn($taxClass);
        $taxClass->expects($this->once())
            ->method('getClassType')
            ->willReturn('CUSTOMER');

        $this->groupResourceModel->expects($this->once())
            ->method('save')
            ->with($this->groupModel)
            ->willThrowException(new LocalizedException(
                new Phrase('Customer Group already exists.')
            ));

        $this->model->save($this->group);
    }

    public function testGetById()
    {
        $groupId = 86;

        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);

        $this->groupDataFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->group);

        $this->group->expects($this->once())
            ->method('setId')
            ->with($groupId)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setCode')
            ->with('Code')
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassId')
            ->with(234)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassName')
            ->with('Tax class name')
            ->willReturnSelf();

        $this->groupModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassName')
            ->willReturn('Tax class name');

        $this->assertSame($this->group, $this->model->getById($groupId));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $groupId = 86;

        $groupExtension = $this->getMockForAbstractClass(GroupExtensionInterface::class);
        $collection = $this->createMock(Collection::class);
        $searchCriteria = $this->getMockForAbstractClass(
            SearchCriteriaInterface::class,
            [],
            '',
            false
        );
        $searchResults = $this->getMockForAbstractClass(
            AddressSearchResultsInterface::class,
            [],
            '',
            false
        );
        $this->searchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);

        $this->groupFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->groupModel);
        $this->groupModel->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, GroupInterface::class);
        $collection->expects($this->once())
            ->method('addTaxClass');
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->groupModel]));

        $this->groupDataFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->group);

        $this->group->expects($this->once())
            ->method('setId')
            ->with($groupId)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setCode')
            ->with('Code')
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassId')
            ->with(234)
            ->willReturnSelf();
        $this->group->expects($this->once())
            ->method('setTaxClassName')
            ->with('Tax class name')
            ->willReturnSelf();

        $this->groupModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn('Code');
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassId')
            ->willReturn(234);
        $this->groupModel->expects($this->atLeastOnce())
            ->method('getTaxClassName')
            ->willReturn('Tax class name');
        $this->groupModel->expects($this->once())
            ->method('getData')
            ->willReturn([]);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('extractExtensionAttributes')
            ->with(GroupInterface::class, [])
            ->willReturn(['extension_attributes' => $groupExtension]);
        $this->group->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($groupExtension);
        $collection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(9);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(9);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$this->group])
            ->willReturnSelf();

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    public function testDeleteById()
    {
        $groupId = 6;
        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);
        $this->groupModel->expects($this->once())
            ->method('usesAsDefault')
            ->willReturn(false);
        $this->groupModel->expects($this->once())
            ->method('delete');
        $this->groupRegistry
            ->expects($this->once())
            ->method('remove')
            ->with($groupId);

        $this->assertTrue($this->model->deleteById($groupId));
    }

    public function testDelete()
    {
        $groupId = 6;
        $this->group->expects($this->once())
            ->method('getId')
            ->willReturn($groupId);
        $this->groupRegistry->expects($this->once())
            ->method('retrieve')
            ->with($groupId)
            ->willReturn($this->groupModel);
        $this->groupModel->expects($this->once())
            ->method('usesAsDefault')
            ->willReturn(false);
        $this->groupModel->expects($this->once())
            ->method('delete');
        $this->groupRegistry
            ->expects($this->once())
            ->method('remove')
            ->with($groupId);

        $this->assertTrue($this->model->delete($this->group));
    }
}
