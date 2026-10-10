<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use PHPUnit\Framework\Attributes\CoversClass;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Catalog\Api\Data\ProductExtensionInterface;

#[CoversClass(\Magento\ConfigurableProduct\Model\Product\ReadHandler::class)]
class ReadHandlerTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Loader|MockObject
     */
    private $optionLoader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->optionLoader = $this->createPartialMock(Loader::class, ['load']);

        $this->readHandler = new ReadHandler($this->optionLoader);
    }

    public function testExecuteWithInvalidProductType()
    {
        $product = $this->createPartialMock(Product::class, ['getTypeId', 'getExtensionAttributes']);

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects(static::never())
            ->method('getExtensionAttributes');

        $entity = $this->readHandler->execute($product);
        static::assertSame($product, $entity);
    }

    public function testExecute()
    {
        $options = [
            ['value_index' => 12],
            ['value_index' => 13]
        ];
        $entityId = 1;
        $ids = [1, 2, 3];

        $product = $this->createPartialMock(Product::class, [
                'getTypeId', 'getId', 'getExtensionAttributes', 'setExtensionAttributes', 'getTypeInstance'
            ]);

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $extensionAttributes = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            [
                'getConfigurableProductOptions', 'setConfigurableProductOptions',
                'getConfigurableProductLinks', 'setConfigurableProductLinks'
            ]
        );

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionLoader->expects(static::once())
            ->method('load')
            ->with($product)
            ->willReturn($options);

        $typeInstance = $this->createPartialMock(Configurable::class, ['getChildrenIds']);

        $product->expects(static::once())
            ->method('getTypeInstance')
            ->willReturn($typeInstance);

        $product->expects(static::once())
            ->method('getId')
            ->willReturn($entityId);

        $typeInstance->expects(static::once())
            ->method('getChildrenIds')
            ->willReturn($ids);

        $product->expects(static::once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributes);

        $entity = $this->readHandler->execute($product);
        static::assertSame($product, $entity);
    }
}
