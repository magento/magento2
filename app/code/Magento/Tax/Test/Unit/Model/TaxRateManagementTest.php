<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

class TaxRateManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Model\TaxRateManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRuleRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRateRepositoryMock;

    protected function setUp()
    {
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->taxRuleRepositoryMock = $this->createMock(\Magento\Tax\Api\TaxRuleRepositoryInterface::class);
        $this->taxRateRepositoryMock = $this->createMock(\Magento\Tax\Api\TaxRateRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->model = new \Magento\Tax\Model\TaxRateManagement(
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
        $productFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $customerFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $searchResultsMock = $this->createMock(\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface::class);
        $taxRuleMock = $this->createMock(\Magento\Tax\Api\Data\TaxRuleInterface::class);
        $taxRateMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateInterface::class);

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
