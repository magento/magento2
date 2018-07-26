<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Test\Unit\Block\Adminhtml\Sales\Coupons;

/**
 * Test for class \Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid
     */
    private $model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Report\Collection\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceFactoryMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->resourceFactoryMock = $this
            ->getMockBuilder(\Magento\Reports\Model\ResourceModel\Report\Collection\Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $aggregatedColumns = [1 => 'SUM(value)'];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid::class,
            [
                '_storeManager' => $this->storeManagerMock,
                '_aggregatedColumns' => $aggregatedColumns,
                'resourceFactory' => $this->resourceFactoryMock,
            ]
        );
    }

    /**
     * @dataProvider getCountTotalsDataProvider
     *
     * @param \Magento\Framework\DataObject $filterData
     * @param \PHPUnit_Framework_MockObject_MockObject $collection
     * @param bool $expectedCountTotals
     */
    public function testGetCountTotals(
        \Magento\Framework\DataObject $filterData,
        \PHPUnit_Framework_MockObject_MockObject $collection,
        bool $expectedCountTotals
    ) {
        $this->model->setFilterData($filterData);

        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMock();
        $this->storeManagerMock->method('getStores')
            ->willReturn([1 => $store]);
        $this->resourceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertEquals($expectedCountTotals, $this->model->getCountTotals());
    }

    /**
     * @return array
     */
    public function getCountTotalsDataProvider(): array
    {
        $filterData = new \Magento\Framework\DataObject();
        $filterData->setData('period_type', 'day');
        $filterData->setData('from', '2000-01-01');
        $filterData->setData('to', '2000-01-30');
        $filterData->setData('store_ids', '1');
        $filterData->setData('price_rule_type', 1);
        $filterData->setData('rules_list', ['0,1']);
        $filterData->setData('order_statuses', 'statuses');

        $emptyCollectionMock = $this->buildBaseCollectionMock($filterData);
        $emptyCollectionMock->expects($this->atLeastOnce())
            ->method('count')
            ->willReturn(0);

        $collectionMock = $this->buildBaseCollectionMock($filterData);
        $collectionMock->expects($this->atLeastOnce())
            ->method('count')
            ->willReturn(1);
        $itemMock = $this->getMockBuilder(\Magento\Reports\Model\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->atLeastOnce())
            ->method('getFirstItem')
            ->willReturn($itemMock);

        return [
            [$filterData, $emptyCollectionMock, false],
            [$filterData, $collectionMock, true],
        ];
    }

    /**
     * @param \Magento\Framework\DataObject $filterData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildBaseCollectionMock(
        \Magento\Framework\DataObject $filterData
    ): \PHPUnit_Framework_MockObject_MockObject {
        $collectionMethods = [
            'setPeriod',
            'setDateRange',
            'addStoreFilter',
            'setAggregatedColumns',
            'isTotals',
            'addRuleFilter',
            'addOrderStatusFilter',
            'count',
            'getFirstItem',
        ];
        $collectionMock = $this
            ->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class)
            ->disableOriginalConstructor()
            ->setMethods($collectionMethods)
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('setPeriod')
            ->with($filterData->getData('period_type'))
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setDateRange')
            ->with($filterData->getData('from'), $filterData->getData('to'))
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with(\explode(',', $filterData->getData('store_ids')))
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setAggregatedColumns')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('isTotals')
            ->with(true)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addOrderStatusFilter')
            ->with($filterData->getData('order_statuses'))
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addRuleFilter')
            ->with(\explode(',', $filterData->getData('rules_list')[0]))
            ->willReturnSelf();

        return $collectionMock;
    }
}
