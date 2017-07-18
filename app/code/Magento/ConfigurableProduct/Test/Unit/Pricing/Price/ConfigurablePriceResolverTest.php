<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigurablePriceResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LowestPriceOptionsProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lowestPriceOptionsProvider;

    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver
     */
    protected $resolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface
     */
    protected $priceResolver;

    protected function setUp()
    {
        $className = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class;
        $this->configurable = $this->getMock($className, ['getUsedProducts'], [], '', false);

        $className = \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface::class;
        $this->priceResolver = $this->getMockForAbstractClass($className, [], '', false, true, true, ['resolvePrice']);

        $this->lowestPriceOptionsProvider = $this->getMock(LowestPriceOptionsProviderInterface::class);

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(
            \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver::class,
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
     * @dataProvider testResolvePriceDataProvider
     */
    public function testResolvePrice($expectedValue)
    {
        $price = $expectedValue;

        $product = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()->getMock();

        $product->expects($this->never())->method('getSku');

        $this->lowestPriceOptionsProvider->expects($this->once())->method('getProducts')->willReturn([$product]);
        $this->priceResolver->expects($this->once())
            ->method('resolvePrice')
            ->with($product)
            ->willReturn($price);

        $this->assertEquals($expectedValue, $this->resolver->resolvePrice($product));
    }

    /**
     * @return array
     */
    public function testResolvePriceDataProvider()
    {
        return [
            'price of zero' => [0.00],
            'price of five' => [5],
        ];
    }
}
