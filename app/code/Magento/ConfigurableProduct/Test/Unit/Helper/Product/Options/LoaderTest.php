<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Helper\Product\Options;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class LoaderTest
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionValueInterfaceFactory|MockObject
     */
    private $optionValueFactory;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Configurable|MockObject
     */
    private $configurable;

    /**
     * @var Loader
     */
    private $loader;

    protected function setUp()
    {
        $this->optionValueFactory = $this->getMockBuilder(OptionValueInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance'])
            ->getMock();

        $this->configurable = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableAttributeCollection'])
            ->getMock();

        $this->loader = new Loader($this->optionValueFactory);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Helper\Product\Options\Loader::load
     */
    public function testLoad()
    {
        $option = [
            'value_index' => 23
        ];

        $this->product->expects(static::once())
            ->method('getTypeInstance')
            ->willReturn($this->configurable);

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'setValues'])
            ->getMock();
        $attributes = [$attribute];

        $this->configurable->expects(static::once())
            ->method('getConfigurableAttributeCollection')
            ->with($this->product)
            ->willReturn($attributes);

        $attribute->expects(static::once())
            ->method('getOptions')
            ->willReturn([$option]);

        $optionValue = $this->getMockForAbstractClass(OptionValueInterface::class);
        $this->optionValueFactory->expects(static::once())
            ->method('create')
            ->willReturn($optionValue);
        $optionValue->expects(static::once())
            ->method('setValueIndex')
            ->with($option['value_index']);

        $attribute->expects(static::once())
            ->method('setValues')
            ->with([$optionValue]);

        $options = $this->loader->load($this->product);
        static::assertSame([$attribute], $options);
    }
}
