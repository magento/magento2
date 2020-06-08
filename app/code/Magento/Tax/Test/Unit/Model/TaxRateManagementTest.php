<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\Data\TaxRuleSearchResultsInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\TaxRateManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxRateManagementTest extends TestCase
{
    /**
     * @var TaxRateManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $taxRuleRepositoryMock;

    /**
     * @var MockObject
     */
    protected $taxRateRepositoryMock;

    protected function setUp(): void
    {
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->taxRuleRepositoryMock = $this->getMockForAbstractClass(TaxRuleRepositoryInterface::class);
        $this->taxRateRepositoryMock = $this->getMockForAbstractClass(TaxRateRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->model = new TaxRateManagement(
            $this->taxRuleRepositoryMock,
            $this->taxRateRepositoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock
        );
    }

    public function testGetRatesByCustomerAndProductTaxClassId()
    {
        $customerTaxClassId = 4;
        $productTaxClassId = 42;
        $rateIds = [10];
        $productFilterMock = $this->createMock(Filter::class);
        $customerFilterMock = $this->createMock(Filter::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchResultsMock = $this->getMockForAbstractClass(TaxRuleSearchResultsInterface::class);
        $taxRuleMock = $this->getMockForAbstractClass(TaxRuleInterface::class);
        $taxRateMock = $this->getMockForAbstractClass(TaxRateInterface::class);

        $this->filterBuilderMock->expects($this->exactly(2))->method('setField')->withConsecutive(
            ['customer_tax_class_ids'],
            ['product_tax_class_ids']
        )->willReturnSelf();
        $this->filterBuilderMock->expects($this->exactly(2))->method('setValue')->withConsecutive(
            [$this->equalTo([$customerTaxClassId])],
            [$this->equalTo([$productTaxClassId])]
        )->willReturnSelf();
        $this->filterBuilderMock->expects($this->exactly(2))->method('create')->willReturnOnConsecutiveCalls(
            $customerFilterMock,
            $productFilterMock
        );
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))->method('addFilters')->withConsecutive(
            [[$customerFilterMock]],
            [[$productFilterMock]]
        );
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $this->taxRuleRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);
        $searchResultsMock->expects($this->once())->method('getItems')->willReturn([$taxRuleMock]);
        $taxRuleMock->expects($this->once())->method('getTaxRateIds')->willReturn($rateIds);
        $this->taxRateRepositoryMock->expects($this->once())->method('get')->with($rateIds[0])
            ->willReturn($taxRateMock);
        $this->assertEquals(
            [$taxRateMock],
            $this->model->getRatesByCustomerAndProductTaxClassId($customerTaxClassId, $productTaxClassId)
        );
    }
}
