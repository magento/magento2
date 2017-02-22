<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

class TaxRateManagementTest extends \PHPUnit_Framework_TestCase
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
        $this->filterBuilderMock = $this->getMock('\Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->taxRuleRepositoryMock = $this->getMock('\Magento\Tax\Api\TaxRuleRepositoryInterface', [], [], '', false);
        $this->taxRateRepositoryMock = $this->getMock('\Magento\Tax\Api\TaxRateRepositoryInterface', [], [], '', false);
        $this->searchCriteriaBuilderMock = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
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
        $productFilterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $customerFilterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $searchResultsMock = $this->getMock('\Magento\Tax\Api\Data\TaxRuleSearchResultsInterface', [], [], '', false);
        $taxRuleMock = $this->getMock('\Magento\Tax\Api\Data\TaxRuleInterface', [], [], '', false);
        $taxRateMock = $this->getMock('\Magento\Tax\Api\Data\TaxRateInterface', [], [], '', false);

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
