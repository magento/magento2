<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Source;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer\Source\Group;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $model;

    /**
     * @var Manager|MockObject
     */
    private $moduleManagerMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var GroupSearchResultsInterface|MockObject
     */
    private $searchResultMock;

    protected function setUp(): void
    {
        $this->moduleManagerMock = $this->createMock(Manager::class);
        $this->groupRepositoryMock = $this->createMock(GroupRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchResultMock = $this->createMock(GroupSearchResultsInterface::class);

        $this->model = new Group(
            $this->moduleManagerMock,
            $this->groupRepositoryMock,
            $this->searchCriteriaBuilderMock
        );
    }

    public function testToOptionArray()
    {
        $customerGroups = [
            ['label' => __('ALL GROUPS'), 'value' => '32000'],
            ['label' => __('NOT LOGGED IN'), 'value' => '0'],
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

        $groupTest = $this->createMock(GroupInterface::class);
        $groupTest->expects($this->any())->method('getCode')->willReturn(__('NOT LOGGED IN'));
        $groupTest->expects($this->any())->method('getId')->willReturn('0');
        $groups = [$groupTest];

        $this->searchResultMock->expects($this->any())->method('getItems')->willReturn($groups);

        $actualCustomerGroups = $this->model->toOptionArray();

        $this->assertEquals($customerGroups, $actualCustomerGroups);

        foreach ($actualCustomerGroups as $actualCustomerGroup) {
            $this->assertIsString($actualCustomerGroup['value']);
        }
    }
}
