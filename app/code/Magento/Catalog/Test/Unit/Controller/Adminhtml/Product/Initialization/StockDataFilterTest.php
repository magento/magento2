<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StockDataFilterTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var StockDataFilter
     */
    protected $stockDataFilter;

    /** @var MockObject */
    protected $stockConfiguration;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->scopeConfigMock->expects($this->any())->method('getValue')->willReturn(1);

        $this->stockConfiguration = $this->createPartialMock(
            Configuration::class,
            ['getManageStock']
        );

        $this->stockDataFilter = new StockDataFilter($this->scopeConfigMock, $this->stockConfiguration);
    }

    /**
     * @param array $inputStockData
     * @param array $outputStockData
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter::filter
     * @dataProvider filterDataProvider
     */
    public function testFilter(array $inputStockData, array $outputStockData)
    {
        if (isset($inputStockData['use_config_manage_stock']) && $inputStockData['use_config_manage_stock'] === 1) {
            $this->stockConfiguration->expects($this->once())
                ->method('getManageStock')
                ->willReturn($outputStockData['manage_stock']);
        }

        $this->assertEquals($outputStockData, $this->stockDataFilter->filter($inputStockData));
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public function filterDataProvider()
    {
        return [
            'case1' => [
                'inputStockData' => [],
                'outputStockData' => ['use_config_manage_stock' => 0, 'is_decimal_divided' => 0],
            ],
            'case2' => [
                'inputStockData' => ['use_config_manage_stock' => 1],
                'outputStockData' => [
                    'use_config_manage_stock' => 1,
                    'manage_stock' => 1,
                    'is_decimal_divided' => 0,
                ],
            ],
            'case3' => [
                'inputStockData' => [
                    'qty' => StockDataFilter::MAX_QTY_VALUE + 1,
                ],
                'outputStockData' => [
                    'qty' => StockDataFilter::MAX_QTY_VALUE,
                    'is_decimal_divided' => 0,
                    'use_config_manage_stock' => 0,
                ],
            ],
            'case4' => [
                'inputStockData' => ['min_qty' => -1],
                'outputStockData' => ['min_qty' => 0, 'is_decimal_divided' => 0, 'use_config_manage_stock' => 0],
            ],
            'case5' => [
                'inputStockData' => ['is_qty_decimal' => 0],
                'outputStockData' => [
                    'is_qty_decimal' => 0,
                    'is_decimal_divided' => 0,
                    'use_config_manage_stock' => 0,
                ],
            ]
        ];
    }
}
