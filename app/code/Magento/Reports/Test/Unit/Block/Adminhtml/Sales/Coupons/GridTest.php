<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @param string $reportType
     * @param int $priceRuleType
     * @param int $collectionSize
     * @param bool $expectedCountTotals
     */
    public function testGetCountTotals(
        string $reportType,
        int $priceRuleType,
        int $collectionSize,
        bool $expectedCountTotals
    ) {
        $filterData = new \Magento\Framework\DataObject();
        $filterData->setData('report_type', $reportType);
        $filterData->setData('period_type', 'day');
        $filterData->setData('from', '2000-01-01');
        $filterData->setData('to', '2000-01-30');
        $filterData->setData('store_ids', '1');
        $filterData->setData('price_rule_type', $priceRuleType);
        if ($priceRuleType) {
            $filterData->setData('rules_list', ['0,1']);
        }
        $filterData->setData('order_statuses', 'statuses');
        $this->model->setFilterData($filterData);

        $resourceCollectionName = $this->model->getResourceCollectionName();
        $collectionMock = $this->buildBaseCollectionMock($filterData, $resourceCollectionName, $collectionSize);

        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMock();
        $this->storeManagerMock->method('getStores')
            ->willReturn([1 => $store]);
        $this->resourceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->assertEquals($expectedCountTotals, $this->model->getCountTotals());
    }

    /**
     * @return array
     */
    public function getCountTotalsDataProvider(): array
    {
        return [
            ['created_at_shipment', 0, 0, false],
            ['created_at_shipment', 0, 1, true],
            ['updated_at_order', 0, 1, true],
            ['updated_at_order', 1, 1, true],
        ];
    }

    /**
     * @param \Magento\Framework\DataObject $filterData
     * @param string $resourceCollectionName
     * @param int $collectionSize
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildBaseCollectionMock(
        \Magento\Framework\DataObject $filterData,
        string $resourceCollectionName,
        int $collectionSize
    ): \PHPUnit_Framework_MockObject_MockObject {
        $collectionMock = $this->getMockBuilder($resourceCollectionName)
            ->disableOriginalConstructor()
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

        if ($filterData->getData('price_rule_type')) {
            $collectionMock->expects($this->once())
                ->method('addRuleFilter')
                ->with(\explode(',', $filterData->getData('rules_list')[0]))
                ->willReturnSelf();
        }

        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        if ($collectionSize) {
            $itemMock = $this->getMockBuilder(\Magento\Reports\Model\Item::class)
                ->disableOriginalConstructor()
                ->getMock();
            $collectionMock->expects($this->once())
                ->method('getItems')
                ->willReturn([$itemMock]);
        }

        return $collectionMock;
    }
}
