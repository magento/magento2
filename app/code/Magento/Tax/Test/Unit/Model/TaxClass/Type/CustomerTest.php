<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\TaxClass\Type;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\TaxClass\Type\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testIsAssignedToObjects()
    {
        $objectManagerHelper = new ObjectManager($this);

        $searchResultsMock  = $this->getMockBuilder(SearchResults::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn(['randomValue']);

        $filterBuilder = $this->createPartialMock(
            FilterBuilder::class,
            ['setField', 'setValue', 'create']
        );

        $filterBuilder->expects($this->once())->method('setField')->with(
            GroupInterface::TAX_CLASS_ID
        )->willReturnSelf();
        $filterBuilder->expects($this->once())->method('setValue')->willReturnSelf();
        $filterBuilder->expects($this->once())->method('create')->willReturnSelf();

        $filterGroupBuilder = $this->createMock(FilterGroupBuilder::class);
        $searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilters', 'create'])
            ->setConstructorArgs(['filterGroupBuilder' => $filterGroupBuilder])
            ->disableOriginalConstructor()
            ->getMock();

        $expectedSearchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();
        $searchCriteriaBuilder->expects($this->once())->method('addFilters')->willReturnSelf();
        $searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($expectedSearchCriteria);

        $customerGroupServiceMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroupServiceMock->expects($this->once())
            ->method('getList')
            ->with($expectedSearchCriteria)
            ->willReturn($searchResultsMock);

        /** @var Customer $model */
        $model = $objectManagerHelper->getObject(
            Customer::class,
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
