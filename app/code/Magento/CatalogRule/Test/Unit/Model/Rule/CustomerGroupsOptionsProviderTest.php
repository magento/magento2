<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Rule;

class CustomerGroupsOptionsProviderTest extends \PHPUnit_Framework_TestCase
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
        $this->groupRepositoryMock = $this->getMock('\Magento\Customer\Api\GroupRepositoryInterface');
        $this->searchCriteriaBuilderMock = $this->getMock(
            '\Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->objectConverterMock = $this->getMock('\Magento\Framework\Convert\DataObject', [], [], '', false);
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

        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $searchResultMock = $this->getMock('\Magento\Customer\Api\Data\GroupSearchResultsInterface');
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
