<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Initialization;

use \Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;

/**
 * Class StockDataFilterTest
 */
class StockDataFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var StockDataFilter
     */
    protected $stockDataFilter;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockConfiguration;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue(1));

        $this->stockConfiguration = $this->getMock(
            'Magento\CatalogInventory\Model\Configuration',
            ['getManageStock'],
            [],
            '',
            false
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
                ->will($this->returnValue($outputStockData['manage_stock']));
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
