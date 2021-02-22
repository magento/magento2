<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use \Magento\Tax\Model\TaxRateCollection;
 
class TaxRateCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TaxRateCollection
     */
    protected $model;
    
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sortOrderBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rateServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rateConverterMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $taxRateMock;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->searchCriteriaBuilderMock =
            $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->sortOrderBuilderMock = $this->createMock(\Magento\Framework\Api\SortOrderBuilder::class);
        $this->rateServiceMock = $this->createPartialMock(\Magento\Tax\Api\TaxRateRepositoryInterface::class, [
                'save',
                'get',
                'deleteById',
                'getList',
                'delete',
                '__wakeup'
            ]);
        $this->rateConverterMock = $this->createMock(\Magento\Tax\Model\Calculation\Rate\Converter::class);
        $this->searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchResultsMock = $this->createMock(\Magento\Tax\Api\Data\TaxRateSearchResultsInterface::class);
        $this->taxRateMock = $this->createMock(\Magento\Tax\Model\Calculation\Rate::class);

        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->model = new TaxRateCollection(
            $this->entityFactoryMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->sortOrderBuilderMock,
            $this->rateServiceMock,
            $this->rateConverterMock
        );
    }

    public function testLoadData()
    {
        $this->rateServiceMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);

        $this->searchResultsMock->expects($this->once())->method('getTotalCount')->willReturn(123);

        $this->searchResultsMock->expects($this->once())->method('getItems')->willReturn([$this->taxRateMock]);
        $this->taxRateMock->expects($this->once())->method('getId')->willReturn(33);
        $this->taxRateMock->expects($this->once())->method('getCode')->willReturn(44);
        $this->taxRateMock->expects($this->once())->method('getTaxCountryId')->willReturn('CountryId');
        $this->taxRateMock->expects($this->once())->method('getTaxRegionId')->willReturn(55);
        $this->taxRateMock->expects($this->once())->method('getRegionName')->willReturn('Region Name');
        $this->taxRateMock->expects($this->once())->method('getTaxPostcode')->willReturn('Post Code');
        $this->taxRateMock->expects($this->once())->method('getRate')->willReturn(1.85);
        $this->rateConverterMock->expects($this->once())
            ->method('createTitleArrayFromServiceObject')
            ->with($this->taxRateMock)
            ->willReturn([]);
        $this->taxRateMock->expects($this->once())->method('getZipTo')->willReturn(null);
        $this->taxRateMock->expects($this->never())->method('getZipFrom');

        $this->model->loadData();
    }

    public function testCreateTaxRateCollectionItem()
    {
        $this->rateServiceMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);

        $this->searchResultsMock->expects($this->once())->method('getTotalCount')->willReturn(123);
        $this->searchResultsMock->expects($this->once())->method('getItems')->willReturn([$this->taxRateMock]);
        $this->taxRateMock->expects($this->once())->method('getId')->willReturn(33);
        $this->taxRateMock->expects($this->once())->method('getCode')->willReturn(44);
        $this->taxRateMock->expects($this->once())->method('getTaxCountryId')->willReturn('CountryId');
        $this->taxRateMock->expects($this->once())->method('getTaxRegionId')->willReturn(55);
        $this->taxRateMock->expects($this->once())->method('getRegionName')->willReturn('Region Name');
        $this->taxRateMock->expects($this->once())->method('getTaxPostcode')->willReturn('Post Code');
        $this->taxRateMock->expects($this->once())->method('getRate')->willReturn(1.85);
        $this->rateConverterMock->expects($this->once())
            ->method('createTitleArrayFromServiceObject')
            ->with($this->taxRateMock)
            ->willReturn([]);
        $this->taxRateMock->expects($this->exactly(2))->method('getZipTo')->willReturn(1);
        $this->taxRateMock->expects($this->exactly(2))->method('getZipFrom')->willReturn(200);

        $this->model->loadData();
    }
}
