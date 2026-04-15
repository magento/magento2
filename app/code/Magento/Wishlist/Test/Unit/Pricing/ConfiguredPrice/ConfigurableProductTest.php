<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;

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
        $this->priceInfoMock = $this->createMock(PriceInfoInterface::class);

        $this->saleableItem = $this->createPartialMock(
            Product::class,
            ['getCustomOption', 'getPriceInfo']
        );

        $this->calculator = $this->createMock(CalculatorInterface::class);

        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);

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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[DataProvider('setOptionsDataProvider')]
    public function testGetValue(array $options, $optionIds)
    {
        $priceValue = 10;
        $customPrice = 100;

        $priceMock = $this->createMock(PriceInterface::class);
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->priceInfoMock = $this->createMock(Base::class);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $wishlistItemOptionMock = $this->createMock(Option::class);
        $wishlistItemOptionMock->expects($this->exactly(2))
            ->method('getProduct')->willReturn($productMock);

        $this->saleableItem->expects($this->any())
            ->method('getCustomOption')
            ->willReturnCallback(function ($arg1) use ($wishlistItemOptionMock) {
                if ($arg1 == 'simple_product') {
                    return $wishlistItemOptionMock;
                } elseif ($arg1 == 'option_ids') {
                    return $wishlistItemOptionMock;
                }
            });

        $wishlistItemOptionMock->expects($this->any())
            ->method('getValue')->willReturn($optionIds);

        $wishlistItemOptionMock->expects($this->exactly(2))
            ->method('getProduct')->willReturn($productMock);

        $productOptionMock = $this->createMock(ProductOption::class);

        $defaultTypeMock = $this->createMock(DefaultType::class);

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

        $itemMock = $this->createMock(ItemInterface::class);
        $this->model->setItem($itemMock);

        $optionInterfaceMock = $this->createMock(OptionInterface::class);

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

        $priceMock = $this->createMock(PriceInterface::class);
        $priceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($priceValue);

        $this->saleableItem->expects($this->any())
            ->method('getCustomOption')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'simple_product' || $arg == 'option_ids') {
                    return null;
                }
            });

        $this->saleableItem->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(ConfigurableProduct::PRICE_CODE)
            ->willReturn($priceMock);

        $this->assertEquals(100, $this->model->getValue());
    }

    public static function setOptionsDataProvider(): array
    {
        return [
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
                ],
                '1'
            ],
            [
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
                ],
                '2'
            ]
        ];
    }
}
