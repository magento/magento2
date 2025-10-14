<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
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
        $this->optionLoader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $this->readHandler = new ReadHandler($this->optionLoader);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\ReadHandler::execute
     */
    public function testExecuteWithInvalidProductType()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects(static::never())
            ->method('getExtensionAttributes');

        $entity = $this->readHandler->execute($product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\ReadHandler::execute
     */
    public function testExecute()
    {
        $options = [
            ['value_index' => 12],
            ['value_index' => 13]
        ];
        $entityId = 1;
        $ids = [1, 2, 3];

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getTypeId', 'getId', 'getExtensionAttributes', 'setExtensionAttributes', 'getTypeInstance'
            ])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->addMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->optionLoader->expects(static::once())
            ->method('load')
            ->with($product)
            ->willReturn($options);

        $typeInstance = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildrenIds'])
            ->getMock();

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
