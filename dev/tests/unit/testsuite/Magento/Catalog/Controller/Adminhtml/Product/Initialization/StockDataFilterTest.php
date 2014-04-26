<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization;
use \Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
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

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue(1));

        $this->stockDataFilter = new StockDataFilter($this->scopeConfigMock);
    }

    /**
     * @param array $inputStockData
     * @param array $outputStockData
     *
     * @covers Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter::filter
     * @dataProvider filterDataProvider
     */
    public function testFilter(array $inputStockData, array $outputStockData)
    {
        $this->assertEquals($outputStockData, $this->stockDataFilter->filter($inputStockData));
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public function filterDataProvider()
    {
        return array(
            'case1' => array(
                'inputStockData' => array(),
                'outputStockData' => array('use_config_manage_stock' => 0, 'is_decimal_divided' => 0)
            ),
            'case2' => array(
                'inputStockData' => array('use_config_manage_stock' => 1),
                'outputStockData' => array(
                    'use_config_manage_stock' => 1,
                    'manage_stock' => 1,
                    'is_decimal_divided' => 0
                )
            ),
            'case3' => array(
                'inputStockData' => array(
                    'qty' => StockDataFilter::MAX_QTY_VALUE + 1
                ),
                'outputStockData' => array(
                    'qty' => StockDataFilter::MAX_QTY_VALUE,
                    'is_decimal_divided' => 0,
                    'use_config_manage_stock' => 0
                )
            ),
            'case4' => array(
                'inputStockData' => array('min_qty' => -1),
                'outputStockData' => array('min_qty' => 0, 'is_decimal_divided' => 0, 'use_config_manage_stock' => 0)
            ),
            'case5' => array(
                'inputStockData' => array('is_qty_decimal' => 0),
                'outputStockData' => array(
                    'is_qty_decimal' => 0,
                    'is_decimal_divided' => 0,
                    'use_config_manage_stock' => 0
                )
            )
        );
    }
}
