<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Sales\Coupons;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid;
use Magento\Reports\Model\Item;
use Magento\Reports\Model\ResourceModel\Report\Collection\Factory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magento\Reports\Block\Adminhtml\Sales\Coupons\Grid
 */
class GridTest extends TestCase
{
    /**
     * @var Grid
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Factory|MockObject
     */
    private $resourceFactoryMock;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->resourceFactoryMock = $this
            ->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $aggregatedColumns = [1 => 'SUM(value)'];

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Grid::class,
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
     * @return void
     */
    public function testGetCountTotals(
        string $reportType,
        int $priceRuleType,
        int $collectionSize,
        bool $expectedCountTotals
    ): void {
        $filterData = new DataObject();
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

        $store = $this->getMockBuilder(StoreInterface::class)
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
     * @return MockObject
     */
    private function buildBaseCollectionMock(
        DataObject $filterData,
        string $resourceCollectionName,
        int $collectionSize
    ): MockObject {
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
            $itemMock = $this->getMockBuilder(Item::class)
                ->disableOriginalConstructor()
                ->getMock();
            $collectionMock->expects($this->once())
                ->method('getItems')
                ->willReturn([$itemMock]);
        }

        return $collectionMock;
    }
}
