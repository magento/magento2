<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class ConfigurablePriceResolverTest extends TestCase
{
    /**
     * @var LowestPriceOptionsProviderInterface|MockObject
     */
    private $lowestPriceOptionsProvider;

    /**
     * @var ConfigurablePriceResolver
     */
    protected $resolver;

    /**
     * @var MockObject|Configurable
     */
    protected $configurable;

    /**
     * @var MockObject|PriceResolverInterface
     */
    protected $priceResolver;

    protected function setUp(): void
    {
        $this->configurable = $this->createPartialMock(Configurable::class, ['getUsedProducts']);

        $this->priceResolver = $this->createPartialMock(PriceResolverInterface::class, ['resolvePrice']);

        $this->lowestPriceOptionsProvider = $this->createMock(LowestPriceOptionsProviderInterface::class);

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(
            ConfigurablePriceResolver::class,
            [
                'priceResolver' => $this->priceResolver,
                'configurable' => $this->configurable,
                'lowestPriceOptionsProvider' => $this->lowestPriceOptionsProvider,
            ]
        );
    }

    /**
     * situation: one product is supplying the price, which could be a price of zero (0)
     *
     *
     * @param $variantPrices
     * @param $expectedPrice
     */
    #[DataProvider('resolvePriceDataProvider')]
    public function testResolvePrice($variantPrices, $expectedPrice)
    {
        $product = $this->createMock(
            Product::class
        );

        $product->expects($this->never())->method('getSku');

        $products = array_map(function () {
            return $this->createMock(Product::class);
        }, $variantPrices);

        $this->lowestPriceOptionsProvider->expects($this->once())->method('getProducts')->willReturn($products);
        $this->priceResolver
            ->method('resolvePrice')
            ->willReturnOnConsecutiveCalls(...$variantPrices);

        $actualPrice = $this->resolver->resolvePrice($product);
        self::assertSame($expectedPrice, $actualPrice);
    }

    /**
     * @return array
     */
    public static function resolvePriceDataProvider()
    {
        return [
            'Single variant at price 0.00 (float), should return 0.00 (float)' => [
                $variantPrices = [
                    0.00,
                ],
                $expectedPrice = 0.00,
            ],
            'Single variant at price 5 (integer), should return 5.00 (float)' => [
                $variantPrices = [
                    5,
                ],
                $expectedPrice = 5.00,
            ],
            'Single variants at price null (null), should return 0.00 (float)' => [
                $variantPrices = [
                    null,
                ],
                $expectedPrice = 0.00,
            ],
            'Multiple variants at price 0, 10, 20, should return 0.00 (float)' => [
                $variantPrices = [
                    0,
                    10,
                    20,
                ],
                $expectedPrice = 0.00,
            ],
            'Multiple variants at price 10, 0, 20, should return 0.00 (float)' => [
                $variantPrices = [
                    10,
                    0,
                    20,
                ],
                $expectedPrice = 0.00,
            ],
        ];
    }
}
