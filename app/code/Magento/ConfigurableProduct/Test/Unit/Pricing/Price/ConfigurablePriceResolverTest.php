<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Pricing\Price;

use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigurablePriceResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ConfigurableOptionsProviderInterface */
    private $cofigurableOptionProvider;

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

        $this->cofigurableOptionProvider = $this->getMockBuilder(ConfigurableOptionsProviderInterface::class)
            ->disableOriginalConstructor()->getMock();


        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(
            'Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver',
            [
                'priceResolver' => $this->priceResolver,
                'configurable' => $this->configurable,
                'configurableOptionsProvider' => $this->cofigurableOptionProvider,
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
        $product = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()->getMock();

        $product->expects($this->once())->method('getSku')->willReturn('Kiwi');

        $this->cofigurableOptionProvider->expects($this->once())->method('getProducts')->willReturn([]);

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

        $product = $this->getMockBuilder(
            \Magento\Catalog\Model\Product::class
        )->disableOriginalConstructor()->getMock();

        $product->expects($this->never())->method('getSku');

        $this->cofigurableOptionProvider->expects($this->once())->method('getProducts')->willReturn([$product]);
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
