<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\TaxClass\Type;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    public function testIsAssignedToObjects()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $searchResultsMock  = $this->getMockBuilder(\Magento\Framework\Api\SearchResults::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(['randomValue']));

        $filterBuilder = $this->createPartialMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setValue', 'create']
        );

        $filterBuilder->expects($this->once())->method('setField')->with(
            \Magento\Customer\Api\Data\GroupInterface::TAX_CLASS_ID
        )->willReturnself();
        $filterBuilder->expects($this->once())->method('setValue')->willReturnself();
        $filterBuilder->expects($this->once())->method('create')->willReturnself();

        $filterGroupBuilder = $this->createMock(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
        $searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->setMethods(['addFilters', 'create'])
            ->setConstructorArgs(['filterGroupBuilder' => $filterGroupBuilder])
            ->disableOriginalConstructor()
            ->getMock();

        $expectedSearchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $searchCriteriaBuilder->expects($this->once())->method('addFilters')->willReturnSelf();
        $searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($expectedSearchCriteria);

        $customerGroupServiceMock = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroupServiceMock->expects($this->once())
            ->method('getList')
            ->with($expectedSearchCriteria)
            ->willReturn($searchResultsMock);

        /** @var $model \Magento\Tax\Model\TaxClass\Type\Customer */
        $model = $objectManagerHelper->getObject(
            \Magento\Tax\Model\TaxClass\Type\Customer::class,
            [
                'customerGroupRepository' => $customerGroupServiceMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilder,
                'filterBuilder' => $filterBuilder,
                'data' => ['id' => 5]
            ]
        );

        $this->assertTrue($model->isAssignedToObjects());
    }
}
