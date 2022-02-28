<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\Option as ProductOption;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Pricing\ConfiguredPrice\ConfigurableProduct;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableProductTest extends TestCase
{
    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableItem;

    /**
     * @var CalculatorInterface|MockObject
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var ConfigurableProduct
     */
    private $model;

    /**
     * @var PriceInfoInterface|MockObject
     */
    private $priceInfoMock;

    protected function setUp(): void
    {
        $this->priceInfoMock = $this->getMockBuilder(PriceInfoInterface::class)
            ->getMockForAbstractClass();

        $this->saleableItem = $this->getMockBuilder(SaleableInterface::class)
            ->setMethods([
                'getPriceInfo',
                'getCustomOption',
            ])
            ->getMockForAbstractClass();

        $this->calculator = $this->getMockBuilder(CalculatorInterface::class)
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ConfigurableProduct(
            $this->saleableItem,
            null,
            $this->calculator,
            $this->priceCurrency
        );
    }

    /**
     * @param array $options
     *
     * @dataProvider setOptionsDataProvider
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetValue(array $options, $optionIds)
    {
        $priceValue = 10;
        $customPrice = 100;

        $priceMock = $this->getMockBuilder(PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $wishlistItemOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistItemOptionMock->expects($this->exactly(2))
            ->method('getProduct')->willReturn($productMock);

        $this->saleableItem->expects($this->any())
            ->method('getCustomOption')
            ->withConsecutive(['simple_product'], ['option_ids'])
            ->willReturnOnConsecutiveCalls($wishlistItemOptionMock, $wishlistItemOptionMock);

        $wishlistItemOptionMock->expects($this->any())
            ->method('getValue')->willReturn($optionIds);

        $wishlistItemOptionMock->expects($this->exactly(2))
            ->method('getProduct')->willReturn($productMock);

        $productOptionMock = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultTypeMock = $this->getMockBuilder(DefaultType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productOptionMock->expects($this->any())
            ->method('getId')
            ->willReturn($options['option_id']);
        $productOptionMock->expects($this->any())
            ->method('getType')
            ->willReturn($options['type']);

        $productOptionMock->expects($this->any())
            ->method('groupFactory')
            ->with($options['type'])
            ->willReturn($defaultTypeMock);
        $productMock->expects($this->any())
            ->method('getOptionById')
            ->with($options['option_id'])->willReturn($productOptionMock);
        $defaultTypeMock->expects($this->any())
            ->method('setOption')
            ->with($productOptionMock)
            ->willReturnSelf();

        $itemMock = $this->getMockForAbstractClass(ItemInterface::class);
        $this->model->setItem($itemMock);

        $optionInterfaceMock = $this->getMockForAbstractClass(OptionInterface::class);

        $itemMock->expects($this->any())
            ->method('getOptionByCode')
            ->with('option_'.$options['option_id'])
            ->willReturn($optionInterfaceMock);

        $optionInterfaceMock->expects($this->any())
            ->method('getValue')
            ->willReturn($productOptionMock);

        $defaultTypeMock->expects($this->any())
            ->method('getOptionPrice')
            ->with($productOptionMock, $priceValue)
            ->willReturn($customPrice);
        $priceValue += $customPrice;

        $this->assertEquals($priceValue, $this->model->getValue());
    }

    public function testGetValueWithNoCustomOption()
    {
        $priceValue = 100;

        $priceMock = $this->getMockBuilder(PriceInterface::class)
            ->getMockForAbstractClass();
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->saleableItem->expects($this->any())
            ->method('getCustomOption')
            ->withConsecutive(['simple_product'], ['option_ids'])
            ->willReturnOnConsecutiveCalls(null, null);

        $this->saleableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $this->assertEquals(100, $this->model->getValue());
    }

    public function setOptionsDataProvider(): array
    {
        return ['options' =>
                [
                    [
                        'option_id' => '1',
                        'product_id' => '2091',
                        'type' => 'checkbox',
                        'is_require' => '1',
                        'default_title' => 'check',
                        'title' => 'check',
                        'default_price' => null,
                        'default_price_type' => null,
                        'price' => null,
                        'price_type' => null
                    ], '1',
                    [
                        'option_id' => '2',
                        'product_id' => '2091',
                        'type' => 'field',
                        'is_require' => '1',
                        'default_title' => 'field',
                        'title' => 'field',
                        'default_price' => '100.000000',
                        'default_price_type' => 'fixed',
                        'price' => '100.000000',
                        'price_type' => 'fixed'
                    ], '2'
                ],
        ];
    }
}
