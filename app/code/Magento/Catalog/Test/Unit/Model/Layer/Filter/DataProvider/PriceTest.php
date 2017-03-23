<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use \Magento\Catalog\Model\Layer\Filter\DataProvider\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Catalog\Model\ResourceModel\Product\Collection|MockObject */
    private $productCollection;

    /** @var \Magento\Catalog\Model\Layer|MockObject */
    private $layer;

    /** @var \Magento\Framework\Registry|MockObject */
    private $coreRegistry;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject */
    private $scopeConfig;

    /** @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price|MockObject */
    private $resource;

    /**
     * @var Price
     */
    private $target;

    protected function setUp()
    {
        $this->productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMaxPrice'])
            ->getMock();
        $this->layer = $this->getMockBuilder(\Magento\Catalog\Model\Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductCollection'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->will($this->returnValue($this->productCollection));
        $this->coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Layer\Filter\Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCount'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Layer\Filter\DataProvider\Price::class,
            [
                'layer' => $this->layer,
                'coreRegistry' => $this->coreRegistry,
                'scopeConfig' => $this->scopeConfig,
                'resource' => $this->resource,
            ]
        );
    }

    public function testInterval()
    {
        $interval = 100500;
        $this->target->setInterval($interval);
        $this->assertSame($interval, $this->target->getInterval());
    }

    public function testConfigValues()
    {
        $map = $this->getValueMap();
        $this->scopeConfig->expects($this->exactly(5))
            ->method('getValue')
            ->will(
                $this->returnCallback(
                    function ($key, $scope) use ($map) {
                        $this->assertArrayHasKey($key, $map);
                        return $map[$key]['scope'] === $scope ? $map[$key]['value'] : null;
                    }
                )
            );
        $this->assertSame($map[Price::XML_PATH_RANGE_CALCULATION]['value'], $this->target->getRangeCalculationValue());
        $this->assertSame($map[Price::XML_PATH_RANGE_STEP]['value'], $this->target->getRangeStepValue());
        $this->assertSame($map[Price::XML_PATH_ONE_PRICE_INTERVAL]['value'], $this->target->getOnePriceIntervalValue());
        $this->assertSame(
            $map[Price::XML_PATH_INTERVAL_DIVISION_LIMIT]['value'],
            $this->target->getIntervalDivisionLimitValue()
        );
        $this->assertSame(
            $map[Price::XML_PATH_RANGE_MAX_INTERVALS]['value'],
            $this->target->getRangeMaxIntervalsValue()
        );
    }

    public function testGetPriceRangeWithRangeInFilter()
    {
        /** @var \Magento\Catalog\Model\Category|MockObject $category */
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilterPriceRange'])
            ->getMock();
        $priceRange = 10;
        $category->expects($this->once())
            ->method('getFilterPriceRange')
            ->will($this->returnValue($priceRange));
        $this->coreRegistry->expects($this->once())
            ->method('registry')
            ->with('current_category_filter')
            ->will($this->returnValue($category));
        $this->target->getPriceRange();
    }

    public function testGetPriceRangeWithRangeCalculation()
    {
        /** @var \Magento\Catalog\Model\Category|MockObject $category */
        $category = $this->getMockBuilder(\Magento\Catalog\Model\Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilterPriceRange'])
            ->getMock();
        $priceRange = 0;
        $category->expects($this->once())
            ->method('getFilterPriceRange')
            ->will($this->returnValue($priceRange));
        $this->coreRegistry->expects($this->once())
            ->method('registry')
            ->with('current_category_filter')
            ->will($this->returnValue($category));
        $maxPrice = 8000;
        $this->productCollection->expects($this->once())
            ->method('getMaxPrice')
            ->will($this->returnValue($maxPrice));
        $this->target->getPriceRange();
    }

    public function testGetMaxPrice()
    {
        $maxPrice = 8000;
        $this->productCollection->expects($this->once())
            ->method('getMaxPrice')
            ->will($this->returnValue($maxPrice));
        $this->assertSame(floatval($maxPrice), $this->target->getMaxPrice());
    }

    /**
     * @dataProvider validateFilterDataProvider
     * @param $filter
     * @param $expectedResult
     */
    public function testValidateFilter($filter, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->target->validateFilter($filter));
    }

    public function validateFilterDataProvider()
    {
        return [
            ['filter' => '0-10', 'result' => ['0', '10']],
            ['filter' => '0-10-20', 'result' => false],
            ['filter' => '', 'result' => false],
            ['filter' => '-', 'result' => ['', '']],
            ['filter' => '0', 'result' => false],
            ['filter' => 0, 'result' => false],
            ['filter' => '100500INF', 'result' => false],
        ];
    }

    public function testGetLayer()
    {
        $this->assertSame($this->layer, $this->target->getLayer());
    }

    public function testGetRangeItemCounts()
    {
        $range = 10;
        $count = [50, 20, 20];
        $this->resource->expects($this->once())
            ->method('getCount')
            ->with($range)
            ->will($this->returnValue($count));
        $this->assertSame($count, $this->target->getRangeItemCounts($range));
    }

    /**
     * @return array
     */
    private function getValueMap()
    {
        return [
            Price::XML_PATH_RANGE_CALCULATION => [
                'scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'value' => 111,
            ],
            Price::XML_PATH_RANGE_STEP => [
                'scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'value' => 222,
            ],
            Price::XML_PATH_ONE_PRICE_INTERVAL => [
                'scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'value' => 333,
            ],
            Price::XML_PATH_INTERVAL_DIVISION_LIMIT => [
                'scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'value' => 444,
            ],
            Price::XML_PATH_RANGE_MAX_INTERVALS => [
                'scope' => \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'value' => 555,
            ],
        ];
    }
}
