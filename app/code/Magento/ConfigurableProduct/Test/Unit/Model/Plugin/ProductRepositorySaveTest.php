<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Plugin\ProductRepositorySave;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductExtensionAttributes;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ProductRepositorySaveTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductRepositorySaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepository;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactory;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Product|MockObject
     */
    private $result;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ProductExtensionAttributes|MockObject
     */
    private $extensionAttributes;

    /**
     * @var ProductAttributeInterface|MockObject
     */
    private $eavAttribute;

    /**
     * @var OptionInterface|MockObject
     */
    private $option;

    /**
     * @var ProductRepositorySave
     */
    private $plugin;

    protected function setUp()
    {
        $this->productAttributeRepository = $this->getMockForAbstractClass(ProductAttributeRepositoryInterface::class);

        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId'])
            ->getMock();

        $this->result = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMock();

        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);

        $this->extensionAttributes = $this->getMockBuilder(ProductExtensionAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->getMockForAbstractClass();

        $this->eavAttribute = $this->getMockForAbstractClass(ProductAttributeInterface::class);

        $this->option = $this->getMockForAbstractClass(OptionInterface::class);

        $this->plugin = (new ObjectManager($this))->getObject(
            ProductRepositorySave::class,
            [
                'productAttributeRepository' => $this->productAttributeRepository,
                'productFactory' => $this->productFactory
            ]
        );
    }

    public function testAfterSaveWhenProductIsSimple()
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');
        $this->product->expects(static::never())
            ->method('getExtensionAttributes');

        $this->assertEquals(
            $this->result,
            $this->plugin->afterSave($this->productRepository, $this->result, $this->product)
        );
    }

    public function testAfterSaveWithoutOptions()
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->result->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);

        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([]);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn([]);

        $this->productAttributeRepository->expects(static::never())
            ->method('get');

        $this->assertEquals(
            $this->result,
            $this->plugin->afterSave($this->productRepository, $this->result, $this->product)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Products "5" and "4" have the same set of attribute values.
     */
    public function testAfterSaveWithLinks()
    {
        $links = [4, 5];
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->result->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->productAttributeRepository->expects(static::never())
            ->method('get');

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getData', '__wakeup'])
            ->getMock();

        $this->productFactory->expects(static::exactly(2))
            ->method('create')
            ->willReturn($product);

        $product->expects(static::exactly(2))
            ->method('load')
            ->willReturnSelf();
        $product->expects(static::never())
            ->method('getData');

        $this->plugin->afterSave($this->productRepository, $this->result, $this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Product with id "4" does not contain required attribute "color".
     */
    public function testAfterSaveWithLinksWithMissingAttribute()
    {
        $simpleProductId = 4;
        $links = [$simpleProductId, 5];
        $attributeCode = 'color';
        $attributeId = 23;

        $this->option->expects(static::once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->result->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$this->option]);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->willReturn($this->eavAttribute);

        $this->eavAttribute->expects(static::once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getData', '__wakeup'])
            ->getMock();

        $this->productFactory->expects(static::once())
            ->method('create')
            ->willReturn($product);
        $product->expects(static::once())
            ->method('load')
            ->with($simpleProductId)
            ->willReturnSelf();
        $product->expects(static::once())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn(false);

        $this->plugin->afterSave($this->productRepository, $this->result, $this->product);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Products "5" and "4" have the same set of attribute values.
     */
    public function testAfterSaveWithLinksWithDuplicateAttributes()
    {
        $links = [4, 5];
        $attributeCode = 'color';
        $attributeId = 23;

        $this->option->expects(static::once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);

        $this->result->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([$this->option]);
        $this->extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->willReturn($this->eavAttribute);

        $this->eavAttribute->expects(static::once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getData', '__wakeup'])
            ->getMock();

        $this->productFactory->expects(static::exactly(2))
            ->method('create')
            ->willReturn($product);
        $product->expects(static::exactly(2))
            ->method('load')
            ->willReturnSelf();
        $product->expects(static::exactly(4))
            ->method('getData')
            ->with($attributeCode)
            ->willReturn($attributeId);

        $this->plugin->afterSave($this->productRepository, $this->result, $this->product);
    }
}
