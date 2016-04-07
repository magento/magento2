<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;

/**
 * Class RuleRepositoryTest
 */
class RuleRepositoryTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->ruleFactory = $this->getMock('\Magento\SalesRule\Model\RuleFactory', ['create'], [], '', false);

        $className = '\Magento\SalesRule\Model\Converter\ToDataModel';
        $this->toDataModelConverter = $this->getMock($className, [], [], '', false);

        $className = '\Magento\SalesRule\Model\Converter\ToModel';
        $this->toModelConverter = $this->getMock($className, [], [], '', false);

        $className = '\Magento\SalesRule\Api\Data\RuleSearchResultInterfaceFactory';
        $this->searchResultFactory = $this->getMock($className, ['create'], [], '', false);

        $className = '\Magento\SalesRule\Api\Data\RuleSearchResultInterface';
        $this->searchResultsMock = $this->getMock($className, [], [], '', false);

        $className = '\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory';
        $this->collectionFactory = $this->getMock($className, ['create'], [], '', false);

        $className = '\Magento\Framework\Api\ExtensionAttribute\JoinProcessor';
        $this->extensionAttributesJoinProcessorMock = $this->getMock($className, ['process'], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->ruleRepository = $objectManager->getObject(
            'Magento\SalesRule\Model\RuleRepository',
            [
                'ruleFactory' => $this->ruleFactory,
                'toDataModelConverter' => $this->toDataModelConverter,
                'toModelConverter' =>  $this->toModelConverter,
                'searchResultFactory' => $this->searchResultFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'ruleCollectionFactory' => $this->collectionFactory
            ]
        );
    }

    public function testDeleteById()
    {
        $model = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('delete');

        $this->assertTrue($this->ruleRepository->deleteById(10));
    }

    public function testGetById()
    {
        $model = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
        $this->ruleFactory->expects($this->once())->method('create')->willReturn($model);
        $model->expects($this->once())->method('load')->with(10)->willReturnSelf();
        $model->expects($this->once())->method('getId')->willReturn(10);
        $model->expects($this->once())->method('getStoreLabels');

        $rule = $this->getMock('\Magento\SalesRule\Model\Data\Rule', [], [], '', false);
        $this->toDataModelConverter->expects($this->once())->method('toDataModel')->with($model)->willReturn($rule);

        $this->assertEquals($rule, $this->ruleRepository->getById(10));
    }

    public function testSave()
    {
        $rule = $this->getMock('\Magento\SalesRule\Model\Data\Rule', [], [], '', false);

        $model = $this->getMock('\Magento\SalesRule\Model\Rule', [], [], '', false);
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
        $currentPage = 42;
        $pageSize = 4;

        /**
         * @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaMock
         */
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $collectionMock = $this->getMock('Magento\SalesRule\Model\ResourceModel\Rule\Collection', [], [], '', false);
        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $sortOrderMock = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, 'Magento\SalesRule\Api\Data\RuleInterface');

        $this->searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())->method('getFilters')->willReturn([$filterMock]);
        $filterMock->expects($this->exactly(2))->method('getConditionType')->willReturn('eq');
        $filterMock->expects($this->once())->method('getField')->willReturn(
            'rule_id'
        );
        $filterMock->expects($this->once())->method('getValue')->willReturn('value');
        $collectionMock->expects($this->once())->method('addFieldToFilter')
            ->with([0 => 'rule_id'], [0 => ['eq' => 'value']]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $this->searchResultsMock->expects($this->once())->method('setTotalCount')->with($collectionSize);
        $searchCriteriaMock->expects($this->once())->method('getSortOrders')->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())->method('getField')->willReturn('sort_order');
        $sortOrderMock->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->once())->method('addOrder')->with('sort_order', 'ASC');
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $collectionMock->expects($this->once())->method('setCurPage')->with($currentPage);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $collectionMock->expects($this->once())->method('setPageSize')->with($pageSize);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->searchResultsMock->expects($this->once())->method('setItems')->with([]);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultsMock);

        $this->assertEquals($this->searchResultsMock, $this->ruleRepository->getList($searchCriteriaMock));
    }
}
