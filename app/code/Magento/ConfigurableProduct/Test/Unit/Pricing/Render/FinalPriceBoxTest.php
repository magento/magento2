<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Render;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;

class FinalPriceBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleableItem;

    /**
     * @var \Magento\Framework\Pricing\Price\PriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $price;

    /**
     * @var \Magento\Framework\Pricing\Render\RendererPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rendererPool;

    /**
     * @var FinalPriceBox
     */
    private $model;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->saleableItem = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->price = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();

        $this->rendererPool = $this->getMockBuilder(\Magento\Framework\Pricing\Render\RendererPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            FinalPriceBox::class,
            [
                'context' => $this->context,
                'saleableItem' => $this->saleableItem,
                'price' => $this->price,
                'rendererPool' => $this->rendererPool,
            ]
        );
    }

    /**
     * @param float $regularPrice
     * @param float $finalPrice
     * @param bool $expected
     * @dataProvider hasSpecialPriceDataProvider
     */
    public function testHasSpecialPrice(
        $regularPrice,
        $finalPrice,
        $expected
    ) {
        $priceMockOne = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();

        $priceMockOne->expects($this->once())
            ->method('getValue')
            ->willReturn($regularPrice);

        $priceMockTwo = $this->getMockBuilder(\Magento\Framework\Pricing\Price\PriceInterface::class)
            ->getMockForAbstractClass();

        $priceMockTwo->expects($this->once())
            ->method('getValue')
            ->willReturn($finalPrice);

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceInfoMock->expects($this->exactly(2))
            ->method('getPrice')
            ->willReturnMap([
                [RegularPrice::PRICE_CODE, $priceMockOne],
                [FinalPrice::PRICE_CODE, $priceMockTwo],
            ]);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getPriceInfo'])
            ->getMockForAbstractClass();

        $productMock->expects($this->exactly(2))
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $typeInstance = $this->getMockBuilder(TypeConfigurable::class)
            ->disableOriginalConstructor()
            ->getMock();

        $typeInstance->expects($this->once())
            ->method('getUsedProducts')
            ->with($this->saleableItem)
            ->willReturn([$productMock]);

        $this->saleableItem->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstance);

        $this->assertEquals($expected, $this->model->hasSpecialPrice());
    }

    /**
     * @return array
     */
    public function hasSpecialPriceDataProvider()
    {
        return [
            [10., 20., false],
            [10., 10., false],
            [20., 10., true],
        ];
    }
}
