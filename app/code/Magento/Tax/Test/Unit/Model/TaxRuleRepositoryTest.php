<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;
use \Magento\Tax\Model\TaxRuleRepository;

class TaxRuleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\TaxRuleRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleRegistry;

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
    protected $ruleFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxRuleRegistry = $this->getMock('\Magento\Tax\Model\Calculation\TaxRuleRegistry', [], [], '', false);
        $this->searchResultFactory = $this->getMock(
            '\Magento\Tax\Api\Data\TaxRuleSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchResultsMock = $this->getMock(
            '\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface',
            [],
            [],
            '',
            false
        );
        $this->ruleFactory = $this->getMock('\Magento\Tax\Model\Calculation\RuleFactory', [], [], '', false);
        $this->collectionFactory = $this->getMock(
            '\Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock('\Magento\Tax\Model\ResourceModel\Calculation\Rule', [], [], '', false);
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            '\Magento\Framework\Api\ExtensionAttribute\JoinProcessor',
            ['process'],
            [],
            '',
            false
        );

        $this->model = new TaxRuleRepository(
            $this->taxRuleRegistry,
            $this->searchResultFactory,
            $this->ruleFactory,
            $this->collectionFactory,
            $this->resource,
            $this->extensionAttributesJoinProcessorMock
        );
    }

    public function testGet()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->assertEquals($rule, $this->model->get(10));
    }

    public function testDelete()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->delete($rule));
    }

    public function testDeleteById()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);

        $rule->expects($this->once())->method('getId')->willReturn(10);
        $this->resource->expects($this->once())->method('delete')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('removeTaxRule')->with(10);
        $this->assertTrue($this->model->deleteById(10));
    }

    public function testSave()
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);

        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->resource->expects($this->once())->method('save')->with($rule);
        $this->taxRuleRegistry->expects($this->once())->method('registerTaxRule')->with($rule);
        $this->assertEquals($rule, $this->model->save($rule));
    }

    /**
     * @dataProvider saveExceptionsDataProvider
     * @param $exceptionObject
     * @param $exceptionName
     * @param $exceptionMessage
     * @throws \Exception
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveWithExceptions($exceptionObject, $exceptionName, $exceptionMessage)
    {
        $rule = $this->getMock('\Magento\Tax\Model\Calculation\Rule', [], [], '', false);
        $rule->expects($this->once())->method('getId')->willReturn(10);

        $this->taxRuleRegistry->expects($this->once())->method('retrieveTaxRule')->with(10)->willReturn($rule);
        $this->resource->expects($this->once())->method('save')->with($rule)
            ->willThrowException($exceptionObject);
        $this->taxRuleRegistry->expects($this->never())->method('registerTaxRule');

        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->model->save($rule);
    }

    /**
     * @return array
     */
    public function saveExceptionsDataProvider()
    {
        return [
            [
                new \Magento\Framework\Exception\LocalizedException(__('Could not save')),
                '\Magento\Framework\Exception\CouldNotSaveException',
                'Could not save'
            ], [
                new \Magento\Framework\Exception\AlreadyExistsException(__('Entity already exists')),
                '\Magento\Framework\Exception\AlreadyExistsException',
                'Entity already exists'
            ], [
                new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity')),
                '\Magento\Framework\Exception\NoSuchEntityException',
                'No such entity'
            ]
        ];
    }

    public function testGetList()
    {
        $collectionSize = 1;
        $currentPage = 42;
        $pageSize = 4;

        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $collectionMock =
            $this->getMock('Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection', [], [], '', false);
        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $sortOrderMock = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock);

        $this->searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->exactly(2))->method('getFilters')->willReturn([$filterMock]);
        $filterMock->expects($this->exactly(2))->method('getConditionType')->willReturn('eq');
        $filterMock->expects($this->exactly(2))->method('getField')->willReturnOnConsecutiveCalls(
            'rate.tax_calculation_rate_id',
            'cd.customer_tax_class_id'
        );
        $filterMock->expects($this->once())->method('getValue')->willReturn('value');
        $collectionMock->expects($this->exactly(2))->method('joinCalculationData')->withConsecutive(['rate'], ['cd']);
        $collectionMock->expects($this->once())->method('addFieldToFilter')
            ->with([0 => 'rate.tax_calculation_rate_id'], [0 => ['eq' => 'value']]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $this->searchResultsMock->expects($this->once())->method('setTotalCount')->with($collectionSize);
        $searchCriteriaMock->expects($this->once())->method('getSortOrders')->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())->method('getField')->willReturn('sort_order');
        $sortOrderMock->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->once())->method('addOrder')->with('position', 'ASC');
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $collectionMock->expects($this->once())->method('setCurPage')->with($currentPage);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $collectionMock->expects($this->once())->method('setPageSize')->with($pageSize);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->searchResultsMock->expects($this->once())->method('setItems')->with([]);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultsMock);
        $this->assertEquals($this->searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
