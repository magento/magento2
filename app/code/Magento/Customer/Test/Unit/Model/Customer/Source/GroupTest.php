<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer\Source;

use Magento\Customer\Model\Customer\Source\Group;
use Magento\Framework\Module\Manager;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Group
     */
    private $model;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManagerMock;

    /**
     * @var GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var GroupSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultMock;

    protected function setUp()
    {
        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultMock = $this->getMockBuilder(GroupSearchResultsInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Group(
            $this->moduleManagerMock,
            $this->groupRepositoryMock,
            $this->searchCriteriaBuilderMock
        );
    }

    public function testToOptionArray()
    {
        $customerGroups = [
            ['label' => __('ALL GROUPS'), 'value' => 32000],
            ['label' => __('NOT LOGGED IN'), 'value' => 0]
        ];

        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->groupRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultMock);
        $this->groupRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultMock);

        $groupTest = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getId'])
            ->getMockForAbstractClass();
        $groupTest->expects($this->any())->method('getCode')->willReturn(__('NOT LOGGED IN'));
        $groupTest->expects($this->any())->method('getId')->willReturn(0);
        $groups = [$groupTest];

        $this->searchResultMock->expects($this->any())->method('getItems')->willReturn($groups);

        $this->assertEquals($customerGroups, $this->model->toOptionArray());
    }
}
