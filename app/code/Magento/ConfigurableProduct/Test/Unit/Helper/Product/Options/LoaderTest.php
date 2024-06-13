<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Helper\Product\Options;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Magento\ConfigurableProduct\Helper\Product\Options\Loader::class)]
class LoaderTest extends TestCase
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

    protected function setUp(): void
    {
        $this->optionValueFactory = $this->createPartialMock(OptionValueInterfaceFactory::class, ['create']);

        $this->product = $this->createPartialMock(Product::class, ['getTypeInstance']);

        $this->configurable = $this->createPartialMock(Configurable::class, ['getConfigurableAttributeCollection']);

        $extensionAttributesJoinProcessor = $this->createMock(JoinProcessorInterface::class);

        $this->loader = new Loader($this->optionValueFactory, $extensionAttributesJoinProcessor);
    }

    public function testLoad()
    {
        $option = [
            'value_index' => 23
        ];

        $this->product->expects(static::once())
            ->method('getTypeInstance')
            ->willReturn($this->configurable);

        $attribute = $this->createPartialMock(Attribute::class, ['getOptions', 'setValues']);

        $attributes = [$attribute];

        $iterator = $this->createMock(Collection::class);
        $iterator->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));

        $this->configurable->expects(static::once())
            ->method('getConfigurableAttributeCollection')
            ->with($this->product)
            ->willReturn($iterator);

        $attribute->expects(static::once())
            ->method('getOptions')
            ->willReturn([$option]);

        $optionValue = $this->createMock(OptionValueInterface::class);
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
