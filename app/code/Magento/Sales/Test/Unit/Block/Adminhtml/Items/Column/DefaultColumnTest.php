<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultColumnTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var DefaultColumn
     */
    protected $defaultColumn;

    /**
     * @var Item|MockObject
     */
    protected $itemMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->defaultColumn = $this->objectManagerHelper->getObject(
            DefaultColumn::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Verify the total amount based on the price including tax flag
     *
     * @dataProvider getScopeConfigSalesPriceDataProvider
     * @param string $taxIncl
     * @param float|null $basePriceInclTax
     * @param float $basePrice
     * @param float $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testGetTotalAmount(string $taxIncl, $priceInclTax, float $price, float $expectedResult): void
    {
        $storeId = 1;
        $discountAmount = 15.21;
        $taxAmount = 0.34;
        $this->itemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->itemMock->expects($this->any())
            ->method('getPriceInclTax')
            ->willReturn($priceInclTax);
        $this->itemMock->expects($this->any())
            ->method('getPrice')
            ->willReturn($price);
        $this->itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn($discountAmount);
        $this->itemMock->expects($this->any())
            ->method('getTaxAmount')
            ->willReturn($taxAmount);
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($taxIncl);
        $this->assertEquals($expectedResult, round($this->defaultColumn->getTotalAmount($this->itemMock), 2));
    }

    /**
     * Verify the total base amount based on the price including tax flag
     *
     * @dataProvider getScopeConfigSalesPriceDataProvider
     * @param string $taxIncl
     * @param float|null $basePriceInclTax
     * @param float $basePrice
     * @param float $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testGetBaseTotalAmount(
        string $taxIncl,
        $basePriceInclTax,
        float $basePrice,
        float $expectedResult
    ): void {
        $storeId = 1;
        $baseDiscountAmount = 15.21;
        $baseTaxAmount = 0.34;
        $this->itemMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->itemMock->expects($this->any())
            ->method('getBasePriceInclTax')
            ->willReturn($basePriceInclTax);
        $this->itemMock->expects($this->any())
            ->method('getBasePrice')
            ->willReturn($basePrice);
        $this->itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->willReturn($baseDiscountAmount);
        $this->itemMock->expects($this->any())
            ->method('getBaseTaxAmount')
            ->willReturn($baseTaxAmount);
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($taxIncl);
        $this->assertEquals($expectedResult, round($this->defaultColumn->getBaseTotalAmount($this->itemMock), 2));
    }

    /**
     * @return array
     */
    public static function getScopeConfigSalesPriceDataProvider(): array
    {
        return [
            ['2', 16.9, 13.52, 1.35],
            ['1', null, 16.9, 1.69],
        ];
    }
}
