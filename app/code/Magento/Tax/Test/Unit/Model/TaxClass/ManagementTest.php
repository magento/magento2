<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\TaxClass;

use \Magento\Tax\Model\TaxClass\Management;

class ManagementTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Management */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $classRepository;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->classRepository = $this->getMock(\Magento\Tax\Model\TaxClass\Repository::class, [], [], '', false);
        $this->searchCriteriaBuilder = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->filterBuilder = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->model = $helper->getObject(
            \Magento\Tax\Model\TaxClass\Management::class,
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
        $taxClassKey = $this->getMock(\Magento\Tax\Api\Data\TaxClassKeyInterface::class);
        $taxClassKey->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\Tax\Api\Data\TaxClassKeyInterface::TYPE_ID);
        $taxClassKey->expects($this->once())->method('getValue')->willReturn('value');
        $this->assertEquals('value', $this->model->getTaxClassId($taxClassKey));
    }

    public function testGetTaxClassIdByNameType()
    {
        $taxClassKey = $this->getMock(\Magento\Tax\Api\Data\TaxClassKeyInterface::class);
        $taxClassKey->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\Tax\Api\Data\TaxClassKeyInterface::TYPE_NAME);
        $taxClassKey->expects($this->once())->method('getValue')->willReturn('value');

        $this->filterBuilder
            ->expects($this->exactly(2))
            ->method('setField')
            ->with(
                $this->logicalOr(
                    \Magento\Tax\Model\ClassModel::KEY_TYPE,
                    \Magento\Tax\Model\ClassModel::KEY_NAME
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

        $filter = $this->getMock(\Magento\Framework\Api\Filter::class, [], [], '', false);
        $this->filterBuilder->expects($this->exactly(2))->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder
            ->expects($this->exactly(2))
            ->method('addFilters')
            ->with([$filter])
            ->willReturnSelf();

        $searchCriteria = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $result = $this->getMock(\Magento\Tax\Api\Data\TaxRateSearchResultsInterface::class);
        $result->expects($this->once())->method('getItems')->willReturn([]);
        $this->classRepository->expects($this->once())->method('getList')->with($searchCriteria)->willReturn($result);

        $this->assertNull($this->model->getTaxClassId($taxClassKey), 'PRODUCT');
    }
}
