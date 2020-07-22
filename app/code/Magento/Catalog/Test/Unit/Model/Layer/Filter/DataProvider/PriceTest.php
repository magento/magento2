<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Layer\Filter\DataProvider;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\ScopeInterface;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
 */
class PriceTest extends TestCase
{
    /** @var  Collection|MockObject */
    private $productCollection;

    /** @var Layer|MockObject */
    private $layer;

    /** @var Registry|MockObject */
    private $coreRegistry;

    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfig;

    /** @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price|MockObject */
    private $resource;

    /**
     * @var Price
     */
    private $target;

    protected function setUp(): void
    {
        $this->productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMaxPrice'])
            ->getMock();
        $this->layer = $this->getMockBuilder(Layer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductCollection'])
            ->getMock();
        $this->layer->expects($this->any())
            ->method('getProductCollection')
            ->willReturn($this->productCollection);
        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Layer\Filter\Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCount'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            Price::class,
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
            ->willReturnCallback(
                function ($key, $scope) use ($map) {
                    $this->assertArrayHasKey($key, $map);
                    return $map[$key]['scope'] === $scope ? $map[$key]['value'] : null;
                }
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
        /** @var Category|MockObject $category */
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilterPriceRange'])
            ->getMock();
        $priceRange = 10;
        $category->expects($this->once())
            ->method('getFilterPriceRange')
            ->willReturn($priceRange);
        $this->coreRegistry->expects($this->once())
            ->method('registry')
            ->with('current_category_filter')
            ->willReturn($category);
        $this->target->getPriceRange();
    }

    public function testGetPriceRangeWithRangeCalculation()
    {
        /** @var Category|MockObject $category */
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFilterPriceRange'])
            ->getMock();
        $priceRange = 0;
        $category->expects($this->once())
            ->method('getFilterPriceRange')
            ->willReturn($priceRange);
        $this->coreRegistry->expects($this->once())
            ->method('registry')
            ->with('current_category_filter')
            ->willReturn($category);
        $maxPrice = 8000;
        $this->productCollection->expects($this->once())
            ->method('getMaxPrice')
            ->willReturn($maxPrice);
        $this->target->getPriceRange();
    }

    public function testGetMaxPrice()
    {
        $maxPrice = 8000;
        $this->productCollection->expects($this->once())
            ->method('getMaxPrice')
            ->willReturn($maxPrice);
        $this->assertSame((float)$maxPrice, $this->target->getMaxPrice());
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

    /**
     * @return array
     */
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
            ['filter' => '-10\'[0]', 'result' => false],
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
            ->willReturn($count);
        $this->assertSame($count, $this->target->getRangeItemCounts($range));
    }

    /**
     * @return array
     */
    private function getValueMap()
    {
        return [
            Price::XML_PATH_RANGE_CALCULATION => [
                'scope' => ScopeInterface::SCOPE_STORE,
                'value' => 111,
            ],
            Price::XML_PATH_RANGE_STEP => [
                'scope' => ScopeInterface::SCOPE_STORE,
                'value' => 222,
            ],
            Price::XML_PATH_ONE_PRICE_INTERVAL => [
                'scope' => ScopeInterface::SCOPE_STORE,
                'value' => 333,
            ],
            Price::XML_PATH_INTERVAL_DIVISION_LIMIT => [
                'scope' => ScopeInterface::SCOPE_STORE,
                'value' => 444,
            ],
            Price::XML_PATH_RANGE_MAX_INTERVALS => [
                'scope' => ScopeInterface::SCOPE_STORE,
                'value' => 555,
            ],
        ];
    }
}
