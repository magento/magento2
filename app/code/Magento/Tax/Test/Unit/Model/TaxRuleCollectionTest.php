<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Tax\Api\Data\TaxRateSearchResultsInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\Calculation\Rule;
use \Magento\Tax\Model\TaxRuleCollection;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

class TaxRuleCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TaxRuleCollection
     */
    protected $model;

    /**
     * @var TaxRuleRepositoryInterface | MockObject
     */
    protected $ruleServiceMock;

    /**
     * @var EntityFactory | MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var FilterBuilder | MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var SearchCriteriaBuilder | MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder | MockObject
     */
    protected $sortOrderBuilderMock;

    /**
     * @var SearchCriteria | MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var TaxRateSearchResultsInterface | MockObject
     */
    protected $searchResultsMock;

    /**
     * @var Rule | MockObject
     */
    protected $taxRuleMock;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->searchCriteriaBuilderMock =
            $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->sortOrderBuilderMock = $this->createMock(\Magento\Framework\Api\SortOrderBuilder::class);
        $this->ruleServiceMock = $this->createMock(\Magento\Tax\Api\TaxRuleRepositoryInterface::class);
        $this->searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchResultsMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateSearchResultsInterface::class);
        $this->taxRuleMock = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getId',
                'getCode',
                'getPriority',
                'getPosition',
                'getCalculateSubtotal',
                'getCustomerTaxClassIds',
                'getProductTaxClassIds',
                'getTaxRateIds',
                'getTaxRatesCodes'
            ])
            ->getMock();

        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->model = new TaxRuleCollection(
            $this->entityFactoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->sortOrderBuilderMock,
            $this->ruleServiceMock
        );
    }

    public function testLoadData()
    {
        $this->ruleServiceMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);

        $this->searchResultsMock->expects($this->once())->method('getTotalCount')->willReturn(568);
        $this->searchResultsMock->expects($this->once())->method('getItems')->willReturn([$this->taxRuleMock]);
        $this->taxRuleMock->expects($this->once())->method('getId')->willReturn(33);
        $this->taxRuleMock->expects($this->once())->method('getCode')->willReturn(44);
        $this->taxRuleMock->expects($this->once())->method('getPriority')->willReturn('some priority');
        $this->taxRuleMock->expects($this->once())->method('getPosition')->willReturn('position');
        $this->taxRuleMock->expects($this->once())->method('getCalculateSubtotal')->willReturn(null);
        $this->taxRuleMock->expects($this->once())->method('getCustomerTaxClassIds')->willReturn('Post Code');
        $this->taxRuleMock->expects($this->once())->method('getProductTaxClassIds')->willReturn([12]);
        $this->taxRuleMock->expects($this->once())->method('getTaxRateIds')->willReturn([66]);
        $this->taxRuleMock->expects($this->once())->method('getTaxRatesCodes')->willReturn(['some_code']);

        $this->model->loadData();
    }
}
