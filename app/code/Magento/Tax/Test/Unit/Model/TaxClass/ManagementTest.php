<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\TaxClass;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxRateSearchResultsInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\TaxClass\Management;
use Magento\Tax\Model\TaxClass\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagementTest extends TestCase
{
    /** @var  Management */
    protected $model;

    /**
     * @var MockObject
     */
    protected $filterBuilder;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var MockObject
     */
    protected $classRepository;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->classRepository = $this->createMock(Repository::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->model = $helper->getObject(
            Management::class,
            [
                'filterBuilder' => $this->filterBuilder,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'classRepository' => $this->classRepository
            ]
        );
    }

    public function testGetTaxClassIdWithoutKey()
    {
        $this->assertNull($this->model->getTaxClassId(null));
    }

    public function testGetTaxClassIdByIDType()
    {
        $taxClassKey = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $taxClassKey->expects($this->once())
            ->method('getType')
            ->willReturn(TaxClassKeyInterface::TYPE_ID);
        $taxClassKey->expects($this->once())->method('getValue')->willReturn('value');
        $this->assertEquals('value', $this->model->getTaxClassId($taxClassKey));
    }

    public function testGetTaxClassIdByNameType()
    {
        $taxClassKey = $this->getMockForAbstractClass(TaxClassKeyInterface::class);
        $taxClassKey->expects($this->once())
            ->method('getType')
            ->willReturn(TaxClassKeyInterface::TYPE_NAME);
        $taxClassKey->expects($this->once())->method('getValue')->willReturn('value');

        $this->filterBuilder
            ->expects($this->exactly(2))
            ->method('setField')
            ->with(
                $this->logicalOr(
                    ClassModel::KEY_TYPE,
                    ClassModel::KEY_NAME
                )
            )->willReturnSelf();

        $this->filterBuilder
            ->expects($this->exactly(2))
            ->method('setValue')
            ->with(
                $this->logicalOr(
                    'PRODUCT',
                    'value'
                )
            )->willReturnSelf();

        $filter = $this->createMock(Filter::class);
        $this->filterBuilder->expects($this->exactly(2))->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder
            ->expects($this->exactly(2))
            ->method('addFilters')
            ->with([$filter])
            ->willReturnSelf();

        $searchCriteria = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $result = $this->getMockForAbstractClass(TaxRateSearchResultsInterface::class);
        $result->expects($this->once())->method('getItems')->willReturn([]);
        $this->classRepository->expects($this->once())->method('getList')->with($searchCriteria)->willReturn($result);

        $this->assertNull($this->model->getTaxClassId($taxClassKey), 'PRODUCT');
    }
}
