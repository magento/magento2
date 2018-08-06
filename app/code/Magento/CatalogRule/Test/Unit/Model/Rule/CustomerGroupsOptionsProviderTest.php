<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class CustomerGroupsOptionsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectConverterMock;

    protected function setup()
    {
        $this->groupRepositoryMock = $this->createMock(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->objectConverterMock = $this->createMock(\Magento\Framework\Convert\DataObject::class);
        $this->model = new \Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider(
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

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $searchResultMock = $this->createMock(\Magento\Customer\Api\Data\GroupSearchResultsInterface::class);
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
