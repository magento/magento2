<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Msrp\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Msrp\Helper\Data;
use Magento\Msrp\Pricing\MsrpPriceCalculatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var MsrpPriceCalculatorInterface|MockObject
     */
    private $msrpPriceCalculator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMsrp'])
            ->onlyMethods(['getPriceInfo', '__wakeup'])
            ->getMock();
        $this->msrpPriceCalculator = $this->getMockBuilder(MsrpPriceCalculatorInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);

        $this->helper = $objectManager->getObject(
            Data::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'msrpPriceCalculator' => $this->msrpPriceCalculator,
            ]
        );
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testIsMinimalPriceLessMsrp()
    {
        $msrp = 120.0;
        $convertedFinalPrice = 200;
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->willReturnCallback(
                function ($arg) {
                    return round(2 * $arg, 2);
                }
            );

        $finalPriceMock = $this->getMockBuilder(FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $finalPriceMock->expects($this->any())
            ->method('getValue')
            ->willReturn($convertedFinalPrice);

        $priceInfoMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($finalPriceMock);

        $this->msrpPriceCalculator
            ->expects($this->any())
            ->method('getMsrpPriceValue')
            ->willReturn($msrp);
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $result = $this->helper->isMinimalPriceLessMsrp($this->productMock);
        $this->assertTrue($result, "isMinimalPriceLessMsrp returned incorrect value");
    }
}
