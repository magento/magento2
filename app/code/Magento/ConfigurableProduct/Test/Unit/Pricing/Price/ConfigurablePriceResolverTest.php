<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigurablePriceResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver
     */
    protected $resolver;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject | \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject | \Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface
     */
    protected $priceResolver;

    protected function setUp()
    {
        $className = 'Magento\ConfigurableProduct\Model\Product\Type\Configurable';
        $this->configurable = $this->getMock($className, ['getUsedProducts'], [], '', false);

        $className = 'Magento\ConfigurableProduct\Pricing\Price\PriceResolverInterface';
        $this->priceResolver = $this->getMockForAbstractClass($className, [], '', false, true, true, ['resolvePrice']);

        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(
            'Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver',
            [
                'priceResolver' => $this->priceResolver,
                'configurable' => $this->configurable,
            ]
        );
    }

    /**
     * situation: There are no used products, thus there are no prices
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testResolvePriceWithNoPrices()
    {
        $product = $this->getMockForAbstractClass(
            'Magento\Framework\Pricing\SaleableInterface',
            [],
            '',
            false,
            true,
            true,
            ['getSku']
        );
        $product->expects($this->once())->method('getSku')->willReturn('Kiwi');

        $this->configurable->expects($this->once())->method('getUsedProducts')->willReturn([]);

        $this->resolver->resolvePrice($product);
    }

    /**
     * situation: one product is supplying the price, which could be a price of zero (0)
     *
     * @dataProvider testResolvePriceDataProvider
     */
    public function testResolvePrice($expectedValue)
    {
        $price = $expectedValue;

        $product = $this->getMockForAbstractClass(
            'Magento\Framework\Pricing\SaleableInterface',
            [],
            '',
            false,
            true,
            true,
            ['getSku']
        );
        $product->expects($this->never())->method('getSku');

        $this->configurable->expects($this->once())->method('getUsedProducts')->willReturn([$product]);
        $this->priceResolver->expects($this->atLeastOnce())->method('resolvePrice')->willReturn($price);

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
