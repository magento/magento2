<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Product\Options\Loader;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ReadHandlerTest
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $this->optionLoader = $this->getMockBuilder(Loader::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
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
            ->setMethods(['getTypeId', 'getExtensionAttributes'])
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
            ->setMethods([
                'getTypeId', 'getId', 'getExtensionAttributes', 'setExtensionAttributes', 'getTypeInstance'
            ])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
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
            ->setMethods(['getChildrenIds'])
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
