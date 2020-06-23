<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Tax\Api\Data\TaxRateSearchResultsInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\TaxRateCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxRateCollectionTest extends TestCase
{
    /**
     * @var TaxRateCollection
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $sortOrderBuilderMock;

    /**
     * @var MockObject
     */
    protected $rateServiceMock;

    /**
     * @var MockObject
     */
    protected $rateConverterMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var MockObject
     */
    protected $searchResultsMock;

    /**
     * @var MockObject
     */
    protected $taxRateMock;

    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->searchCriteriaBuilderMock =
            $this->createMock(SearchCriteriaBuilder::class);
        $this->sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);
        $this->rateServiceMock = $this->getMockBuilder(TaxRateRepositoryInterface::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['save', 'get', 'deleteById', 'getList', 'delete'])
            ->getMockForAbstractClass();
        $this->rateConverterMock = $this->createMock(Converter::class);
        $this->searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $this->searchResultsMock = $this->getMockForAbstractClass(TaxRateSearchResultsInterface::class);
        $this->taxRateMock = $this->createMock(Rate::class);

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
