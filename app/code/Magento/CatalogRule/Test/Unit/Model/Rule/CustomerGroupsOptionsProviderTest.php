<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule;

use Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerGroupsOptionsProviderTest extends TestCase
{
    /**
     * @var CustomerGroupsOptionsProvider
     */
    private $model;

    /**
     * @var MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    private $objectConverterMock;

    protected function setup(): void
    {
        $this->groupRepositoryMock = $this->getMockForAbstractClass(GroupRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->objectConverterMock = $this->createMock(DataObject::class);
        $this->model = new CustomerGroupsOptionsProvider(
            $this->groupRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->objectConverterMock
        );
    }

    public function testToOptionArray()
    {
        $customerGroups = ['group1', 'group2'];

        $options = [
            ['label' => 'label', 'value' => 'value']
        ];

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchResultMock = $this->getMockForAbstractClass(GroupSearchResultsInterface::class);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $this->groupRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())->method('getItems')->willReturn($customerGroups);
        $this->objectConverterMock->expects($this->once())
            ->method('toOptionArray')
            ->with($customerGroups, 'id', 'code')
            ->willReturn($options);

        $this->assertEquals($options, $this->model->toOptionArray());
    }
}
