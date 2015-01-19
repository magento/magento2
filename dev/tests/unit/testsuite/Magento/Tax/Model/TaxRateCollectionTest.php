<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model;
 
class TaxRateCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxRateCollection
     */
    protected $model;
    
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sortOrderBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rateServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rateConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxRateMock;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock('Magento\Core\Model\EntityFactory', [], [], '', false);
        $this->filterBuilderMock = $this->getMock('Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->searchCriteriaBuilderMock =
            $this->getMock('Magento\Framework\Api\SearchCriteriaBuilder', [], [], '', false);
        $this->sortOrderBuilderMock = $this->getMock('Magento\Framework\Api\SortOrderBuilder', [], [], '', false);
        $this->rateServiceMock = $this->getMock(
            'Magento\Tax\Api\TaxRateRepositoryInterface',
            [
                'save',
                'get',
                'deleteById',
                'getList',
                'delete',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->rateConverterMock = $this->getMock(
            'Magento\Tax\Model\Calculation\Rate\Converter',
            [],
            [],
            '',
            false
        );
        $this->searchCriteriaMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaInterface',
            [],
            [],
            '',
            false
        );
        $this->searchResultsMock = $this->getMock(
            'Magento\Tax\Api\Data\TaxRateSearchResultsInterface',
            [],
            [],
            '',
            false
        );
        $this->taxRateMock = $this->getMock('Magento\Tax\Model\Calculation\Rate', [], [], '', false);

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
