<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule\DataProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMock(
            'Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            'Magento\Framework\Api\SearchCriteriaBuilder',
            [],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('Magento\Store\Model\System\Store', [], [], '', false);
        $this->groupRepositoryMock = $this->getMock('Magento\Customer\Api\GroupRepositoryInterface', [], [], '', false);
        $this->dataObjectMock = $this->getMock('Magento\Framework\Convert\DataObject', [], [], '', false);

        $this->collectionMock = $this->getMock(
            'Magento\SalesRule\Model\ResourceModel\Rule\Collection',
            [],
            [],
            '',
            false
        );
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $searchCriteriaMock = $this->getMock('Magento\Framework\Api\SearchCriteriaInterface', [], [], '', false);
        $groupSearchResultsMock = $this->getMock(
            'Magento\Customer\Api\Data\GroupSearchResultsInterface',
            [],
            [],
            '',
            false
        );
        $groupsMock = $this->getMock('Magento\Customer\Api\Data\GroupInterface', [], [], '', false);

        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);
        $this->groupRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($groupSearchResultsMock);
        $groupSearchResultsMock->expects($this->once())->method('getItems')->willReturn([$groupsMock]);
        $this->storeMock->expects($this->once())->method('getWebsiteValuesForForm')->willReturn([]);
        $this->dataObjectMock->expects($this->once())->method('toOptionArray')->with([$groupsMock], 'id', 'code')
            ->willReturn([]);
        $ruleFactoryMock = $this->getMock('Magento\SalesRule\Model\RuleFactory', ['create'], [], '', false);
        $ruleMock = $this->getMock('Magento\SalesRule\Model\Rule', [], [], '', false);
        $ruleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getCouponTypes')
            ->willReturn(
                [
                    'key1' => 'couponType1',
                    'key2' => 'couponType2',
                ]
            );
        $registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getStoreLabels')
            ->willReturn(
                [
                    'label0',
                    'label1',
                ]
            );
        $this->model = (new ObjectManager($this))->getObject(
            'Magento\SalesRule\Model\Rule\DataProvider',
            [
                'name' => 'Name',
                'primaryFieldName' => 'Primary',
                'requestFieldName' => 'Request',
                'collectionFactory' => $this->collectionFactoryMock,
                'store' => $this->storeMock,
                'groupRepository' => $this->groupRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'objectConverter' => $this->dataObjectMock,
                'salesRuleFactory' => $ruleFactoryMock,
                'registry' => $registryMock,
            ]
        );
    }

    public function testGetData()
    {
        $ruleId = 42;
        $ruleData = ['name' => 'Sales Price Rule'];

        $ruleMock = $this->getMock(
            'Magento\SalesRule\Model\Rule',
            [
                'getDiscountAmount',
                'setDiscountAmount',
                'getDiscountQty',
                'setDiscountQty',
                'load',
                'getId',
                'getData'
            ],
            [],
            '',
            false
        );
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$ruleMock]);

        $ruleMock->expects($this->atLeastOnce())->method('getId')->willReturn($ruleId);
        $ruleMock->expects($this->once())->method('load')->willReturnSelf();
        $ruleMock->expects($this->once())->method('getData')->willReturn($ruleData);
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(50.000);
        $ruleMock->expects($this->once())->method('setDiscountAmount')->with(50)->willReturn($ruleMock);
        $ruleMock->expects($this->once())->method('getDiscountQty')->willReturn(20.010);
        $ruleMock->expects($this->once())->method('setDiscountQty')->with(20.01)->willReturn($ruleMock);

        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
        // Load from object-cache the second time
        $this->assertEquals([$ruleId => $ruleData], $this->model->getData());
    }
}
