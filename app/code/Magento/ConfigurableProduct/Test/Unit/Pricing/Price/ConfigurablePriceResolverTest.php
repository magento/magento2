<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\TierPriceInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    protected function setUp()
    {
        $className = Configurable::class;
        $this->configurable = $this->createPartialMock($className, ['getUsedProducts']);

        $className = PriceResolverInterface::class;
        $this->priceResolver = $this->getMockForAbstractClass($className, [], '', false, true, true, ['resolvePrice']);

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
     * @dataProvider resolvePriceDataProvider
     *
     * @param $variantPrices
     * @param $expectedPrice
     */
    public function testResolvePrice($variantPrices, $expectedPrice)
    {
        $product = $this->getMockBuilder(
            Product::class
        )->disableOriginalConstructor()->getMock();

        $product->expects($this->never())->method('getSku');

        $products = array_map(function () {
            return $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
        }, $variantPrices);

        $this->lowestPriceOptionsProvider->expects($this->once())->method('getProducts')->willReturn($products);
        $this->priceResolver
            ->method('resolvePrice')
            ->willReturnOnConsecutiveCalls(...$variantPrices);

        $actualPrice = $this->resolver->resolvePrice($product);
        self::assertSame($expectedPrice, $actualPrice);
    }

    /**
     * situation: configurable product has simple product that contain tier prices
     *
     * @dataProvider resolveTierPriceDataProvider
     *
     * @param $variantPrices
     * @param $expectedPrice
     */
    public function testResolvePriceWithTier($variantPrices, $expectedPrice)
    {
        $product = $this->getMockBuilder(
            Product::class
        )->disableOriginalConstructor()->getMock();

        $product->expects($this->never())->method('getSku');

        $simpleProducts = [];
        $simplePrices = [];
        foreach ($variantPrices as $prices) {
            $simplePrices[] = $prices['simple_price'];
            $tierPrices = $prices['tier_prices'];

            $tierAmounts = [];
            if (!empty($tierPrices)) {
                foreach ($tierPrices as $price) {
                    $tierAmounts[] = $this->getAmountMock($price);
                }
            }

            $tierPriceMock = $this->getTierPriceMock($tierAmounts);
            $priceInfoMock = $this->getPriceInfoMock($tierPriceMock);

            $simpleProduct = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->getMock();
            $simpleProduct->method('getPriceInfo')
                ->willReturn($priceInfoMock);

            array_push($simpleProducts, $simpleProduct);
        }
        $productConfigurable = $this->getProductTypeMock($simpleProducts, $product);
        $product->method('getTypeInstance')
            ->willReturn($productConfigurable);

        $this->lowestPriceOptionsProvider
            ->expects($this->once())
            ->method('getProducts')
            ->willReturn($simpleProducts);
        $this->priceResolver
            ->method('resolvePrice')
            ->willReturnOnConsecutiveCalls(...$simplePrices);

        $actualPrice = $this->resolver->resolvePrice($product);
        self::assertSame($expectedPrice, $actualPrice);
    }

    /**
     * Retrieve mock of \Magento\Framework\Pricing\Amount\AmountInterface object
     *
     * @param float $amount
     * @return MockObject
     */
    private function getAmountMock($amount): MockObject
    {
        $amountMock = $this->getMockBuilder(AmountInterface::class)
            ->setMethods(['getValue', 'getBaseAmount'])
            ->getMockForAbstractClass();
        $amountMock->expects($this->any())
            ->method('getValue')
            ->willReturn($amount);
        $amountMock->expects($this->any())
            ->method('getBaseAmount')
            ->willReturn($amount);

        return $amountMock;
    }

    /**
     * Retrieve mock of \Magento\Catalog\Pricing\Price\TierPriceInterface object
     *
     * @param array $amounts
     * @return MockObject
     */
    private function getTierPriceMock(array $amounts): MockObject
    {
        $tierPrices = [];
        foreach ($amounts as $amount) {
            $tierPrices[] = ['price' => $amount];
        }

        $tierPriceMock = $this->getMockBuilder(TierPriceInterface::class)
            ->setMethods(['getTierPriceList'])
            ->getMockForAbstractClass();
        $tierPriceMock->expects($this->any())
            ->method('getTierPriceList')
            ->willReturn($tierPrices);

        return $tierPriceMock;
    }

    /**
     * Retrieve mock of \Magento\Framework\Pricing\PriceInfo\Base object
     *
     * @param MockObject $tierPriceMock
     * @return MockObject
     */
    private function getPriceInfoMock(MockObject $tierPriceMock): MockObject
    {
        $priceInfoMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getPrice')
            ->willReturnMap(
                [
                    ['tier_price', $tierPriceMock],
                ]
            );
        return $priceInfoMock;
    }

    /**
     * Retrieve mocks of \Magento\ConfigurableProduct\Model\Product\Type\Configurable object
     *
     * @param MockObject[] $productMocks
     * @param MockObject $product
     * @return MockObject
     */
    private function getProductTypeMock(array $productMocks, MockObject $product): MockObject
    {
        $productTypeMock = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productTypeMock->expects($this->any())
            ->method('getUsedProducts')
            ->with($product)
            ->willReturn($productMocks);

        return $productTypeMock;
    }

    /**
     * @return array
     */
    public function resolvePriceDataProvider()
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

    /**
     * @return array
     */
    public function resolveTierPriceDataProvider()
    {
        return [
            'Single variant at simple price 0.00 (float) without tier prices, should return 0.00 (float)' => [
                $variantPrices = [
                    [
                        'simple_price' => 0.00,
                        'tier_prices' => []
                    ]
                ],
                $expectedPrice = 0.00,
            ],
            'Single variant at simple price 5 (integer) with tier prices 4, 11, 22, should return 4.00 (float)' => [
                $variantPrices = [
                    [
                        'simple_price' => 5,
                        'tier_prices' => [4, 11, 22]
                    ]
                ],
                $expectedPrice = 4.00,
            ],
            'Multiple variants at simple prices 0, 10, 20 without tier prices, should return 0.00 (float)' => [
                $variantPrices = [
                    [
                        'simple_price' => 0,
                        'tier_prices' => []
                    ], [
                        'simple_price' => 10,
                        'tier_prices' => []
                    ],
                    [
                        'simple_price' => 20,
                        'tier_prices' => []
                    ]
                ],
                $expectedPrice = 0.00,
            ],
            'Multiple variants at simple price 10 without tier prices, simple price 15 with tier prices 10, 5,' .
            ' simple price 20 with tier prices 15, 10. 4.5, should return 4.5 (float)' => [
                $variantPrices = [
                    [
                        'simple_price' => 10,
                        'tier_prices' => []
                    ], [
                        'simple_price' => 15,
                        'tier_prices' => [10, 5]
                    ], [
                        'simple_price' => 20,
                        'tier_prices' => [15, 10, 4.5]
                    ],
                ],
                $expectedPrice = 4.5,
            ],
        ];
    }
}
