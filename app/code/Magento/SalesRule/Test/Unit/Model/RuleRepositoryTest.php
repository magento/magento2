<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

/**
 * Class RuleRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToDataModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $toDataModelConverter;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $toModelConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->ruleFactory = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);

        $className = \Magento\SalesRule\Model\Converter\ToDataModel::class;
        $this->toDataModelConverter = $this->createMock($className);

        $className = \Magento\SalesRule\Model\Converter\ToModel::class;
        $this->toModelConverter = $this->createMock($className);

        $className = \Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory::class;
        $this->searchResultFactory = $this->createPartialMock($className, ['create']);

        $className = \Magento\SalesRule\Api\Data\RuleSearchResultInterface::class;
        $this->searchResultsMock = $this->createMock($className);

        $className = \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory::class;
        $this->collectionFactory = $this->createPartialMock($className, ['create']);

        $className = \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class;
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock($className, ['process']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
        $this->ruleRepository = $objectManager->getObject(
            \Magento\SalesRule\Model\RuleRepository::class,
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
        $model = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('delete');

        $this->assertTrue($this->ruleRepository->deleteById(10));
    }

    public function testGetById()
    {
        $model = $this->createMock(\Magento\SalesRule\Model\Rule::class);
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

        $model = $this->createMock(\Magento\SalesRule\Model\Rule::class);
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
         * @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaMock
         */
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $collectionMock = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Rule\Collection::class);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, \Magento\SalesRule\Api\Data\RuleInterface::class);

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
