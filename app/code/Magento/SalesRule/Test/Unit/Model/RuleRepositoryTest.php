<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleSearchResultInterface;
use Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory;
use Magento\SalesRule\Model\Converter\ToDataModel;
use Magento\SalesRule\Model\Converter\ToModel;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\RuleRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepositoryTest extends TestCase
{
    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var RuleFactory|MockObject
     */
    protected $ruleFactory;

    /**
     * @var MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    protected $searchResultsMock;

    /**
     * @var MockObject
     */
    protected $collectionFactory;

    /**
     * @var MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var ToDataModel|MockObject
     */
    protected $toDataModelConverter;

    /**
     * @var ToModel|MockObject
     */
    protected $toModelConverter;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    protected function setUp(): void
    {
        $this->ruleFactory = $this->createPartialMock(RuleFactory::class, ['create']);

        $this->toDataModelConverter = $this->createMock(ToDataModel::class);

        $this->toModelConverter = $this->createMock(ToModel::class);

        $this->searchResultFactory = $this->getMockBuilder(RuleSearchResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->searchResultsMock = $this->getMockForAbstractClass(RuleSearchResultInterface::class);

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->extensionAttributesJoinProcessorMock = $this->getMockBuilder(JoinProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['process'])
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );
        $this->ruleRepository = $objectManager->getObject(
            RuleRepository::class,
            [
                'ruleFactory' => $this->ruleFactory,
                'toDataModelConverter' => $this->toDataModelConverter,
                'toModelConverter' =>  $this->toModelConverter,
                'searchResultFactory' => $this->searchResultFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'ruleCollectionFactory' => $this->collectionFactory,
                'collectionProcessor' => $this->collectionProcessor
            ]
        );
    }

    public function testDeleteById()
    {
        $model = $this->createMock(Rule::class);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('delete');

        $this->assertTrue($this->ruleRepository->deleteById(10));
    }

    public function testGetById()
    {
        $model = $this->createMock(Rule::class);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('getStoreLabels');

        $rule = $this->createMock(\Magento\SalesRule\Model\Data\Rule::class);
        $this->toDataModelConverter->expects($this->once())->method('toDataModel')->with($model)->willReturn($rule);

        $this->assertEquals($rule, $this->ruleRepository->getById(10));
    }

    public function testSave()
    {
        $rule = $this->createMock(\Magento\SalesRule\Model\Data\Rule::class);

        $model = $this->createMock(Rule::class);
        $this->toModelConverter->expects($this->once())->method('toModel')->with($rule)->willReturn($model);
        $model->expects($this->once())->method('save');
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('load')->with(10);
        $model->expects($this->once())->method('getStoreLabels');

        $this->toDataModelConverter->expects($this->once())->method('toDataModel')->with($model)->willReturn($rule);

        $this->assertEquals($rule, $this->ruleRepository->save($rule));
    }

    public function testGetList()
    {
        $collectionSize = 1;
        /**
         * @var SearchCriteriaInterface $searchCriteriaMock
         */
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $collectionMock = $this->createMock(Collection::class);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, RuleInterface::class);

        $this->searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $this->searchResultsMock->expects($this->once())->method('setTotalCount')->with($collectionSize);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->searchResultsMock->expects($this->once())->method('setItems')->with([]);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultsMock);

        $this->assertEquals($this->searchResultsMock, $this->ruleRepository->getList($searchCriteriaMock));
    }
}
