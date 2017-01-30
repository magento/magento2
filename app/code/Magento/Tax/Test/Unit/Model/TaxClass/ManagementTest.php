<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

        $this->classRepository = $this->getMock('\Magento\Tax\Model\TaxClass\Repository', [], [], '', false);
        $this->searchCriteriaBuilder = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->filterBuilder = $this->getMock('\Magento\Framework\Api\FilterBuilder', [], [], '', false);
        $this->model = $helper->getObject(
            '\Magento\Tax\Model\TaxClass\Management',
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
        $taxClassKey = $this->getMock('\Magento\Tax\Api\Data\TaxClassKeyInterface');
        $taxClassKey->expects($this->once())
            ->method('getType')
            ->willReturn(\Magento\Tax\Api\Data\TaxClassKeyInterface::TYPE_ID);
        $taxClassKey->expects($this->once())->method('getValue')->willReturn('value');
        $this->assertEquals('value', $this->model->getTaxClassId($taxClassKey));
    }

    public function testGetTaxClassIdByNameType()
    {
        $taxClassKey = $this->getMock('\Magento\Tax\Api\Data\TaxClassKeyInterface');
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

        $filter = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $this->filterBuilder->expects($this->exactly(2))->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder
            ->expects($this->exactly(2))
            ->method('addFilters')
            ->with([$filter])
            ->willReturnSelf();

        $searchCriteria = $this->getMock('\Magento\Framework\Api\SearchCriteriaInterface');
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $result = $this->getMock('\Magento\Tax\Api\Data\TaxRateSearchResultsInterface');
        $result->expects($this->once())->method('getItems')->willReturn([]);
        $this->classRepository->expects($this->once())->method('getList')->with($searchCriteria)->willReturn($result);

        $this->assertNull($this->model->getTaxClassId($taxClassKey), 'PRODUCT');
    }
}
