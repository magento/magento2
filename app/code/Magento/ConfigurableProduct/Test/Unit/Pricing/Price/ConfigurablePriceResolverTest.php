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
        $this->configurable = $this->getMock($className, ['getUsedProductCollection'], [], '', false);

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
        $productCollection = $this->getProductCollection([]);

        $this->configurable->expects($this->once())->method('getUsedProductCollection')->willReturn($productCollection);

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

        $productCollection = $this->getProductCollection([$product]);
        $this->configurable->expects($this->once())->method('getUsedProductCollection')->willReturn($productCollection);
        $this->priceResolver->expects($this->atLeastOnce())->method('resolvePrice')->willReturn($price);

        $this->assertEquals($expectedValue, $this->resolver->resolvePrice($product));
    }

    /**
     * @param array $products
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductCollection($products)
    {
        $productCollection = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection'
        )->setMethods(
            [
                'setFlag',
                'addAttributeToSelect',
                'getIterator',
            ]
        )->disableOriginalConstructor()
            ->getMock();
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturnSelf();
        $productCollection->expects($this->once())->method('setFlag')->willReturnSelf();
        $productCollection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator($products));

        return $productCollection;
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
