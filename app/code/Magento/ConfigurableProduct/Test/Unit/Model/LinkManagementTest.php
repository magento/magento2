<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagementTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $productRepository;

    /**
     * @var MockObject
     */
    protected $productFactory;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject
     */
    protected $configurableType;

    /**
     * @var LinkManagement
     */
    protected $object;

    /**
     * @var MockObject|DataObjectHelper
     */
    protected $dataObjectHelperMock;

    protected function setUp(): void
    {
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->objectManagerHelper = new ObjectManager($this);
        $this->productFactory = $this->createPartialMock(
            ProductInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableType =
            $this->getMockBuilder(\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->object = $this->objectManagerHelper->getObject(
            LinkManagement::class,
            [
                'productRepository' => $this->productRepository,
                'productFactory' => $this->productFactory,
                'configurableType' => $this->configurableType,
                'dataObjectHelper' => $this->dataObjectHelperMock,
            ]
        );
    }

    public function testGetChildren()
    {
        $productId = 'test';

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance = $this->getMockBuilder(
            Configurable::class
        )->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->any())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $product->expects($this->any())->method('getStoreId')->willReturn(1);
        $product->expects($this->any())->method('getTypeInstance')->willReturn($productTypeInstance);
        $productTypeInstance->expects($this->once())->method('setStoreFilter')->with(1, $product);

        $childProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance->expects($this->any())->method('getUsedProducts')
            ->with($product)->willReturn([$childProduct]);

        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->willReturn($product);

        $attribute = $this->getMockForAbstractClass(AttributeInterface::class);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn('code');
        $childProduct->expects($this->once())->method('getDataUsingMethod')->with('code')->willReturn(false);
        $childProduct->expects($this->once())->method('getData')->with('code')->willReturn(10);
        $childProduct->expects($this->once())->method('getStoreId')->willReturn(1);
        $childProduct->expects($this->once())->method('getAttributes')->willReturn([$attribute]);

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($productMock, ['store_id' => 1, 'code' => 10], ProductInterface::class)
            ->willReturnSelf();

        $this->productFactory->expects($this->once())
            ->method('create')
            ->willReturn($productMock);

        $products = $this->object->getChildren($productId);
        $this->assertCount(1, $products);
        $this->assertEquals($productMock, $products[0]);
    }

    public function testGetWithNonConfigurableProduct()
    {
        $productId= 'test';
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())->method('getTypeId')->willReturn('simple');
        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->willReturn($product);

        $this->assertEmpty($this->object->getChildren($productId));
    }

    public function testAddChild()
    {
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getExtensionAttributes'])
            ->getMock();
        $simple = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getData'])
            ->getMock();
        $extensionAttributesMock = $this->getMockBuilder(ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getConfigurableProductOptions',
                    'setConfigurableProductOptions',
                    'setConfigurableProductLinks'
                ]
            )
            ->getMockForAbstractClass();
        $optionMock = $this->getMockBuilder(OptionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', 'getPosition', 'getAttributeId'])
            ->getMockForAbstractClass();
        $productAttributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $optionsFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $reflectionClass = new \ReflectionClass(LinkManagement::class);
        $optionsFactoryReflectionProperty = $reflectionClass->getProperty('optionsFactory');
        $optionsFactoryReflectionProperty->setAccessible(true);
        $optionsFactoryReflectionProperty->setValue($this->object, $optionsFactoryMock);

        $attributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $attributeFactoryReflectionProperty = $reflectionClass->getProperty('attributeFactory');
        $attributeFactoryReflectionProperty->setAccessible(true);
        $attributeFactoryReflectionProperty->setValue($this->object, $attributeFactoryMock);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection', 'getOptions', 'getId', 'getAttributeCode', 'getStoreLabel'])
            ->getMock();
        $attributeOptionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getLabel'])
            ->getMock();
        $attributeCollectionMock = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getItems'])
            ->getMock();

        $this->productRepository->expects($this->at(0))->method('get')->with($productSku)->willReturn($configurable);
        $this->productRepository->expects($this->at(1))->method('get')->with($childSku)->willReturn($simple);

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->willReturn(
                [0 => [1, 2, 3]]
            );

        $configurable->expects($this->any())->method('getId')->willReturn(666);
        $simple->expects($this->any())->method('getId')->willReturn(999);

        $configurable->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $extensionAttributesMock->expects($this->any())
            ->method('getConfigurableProductOptions')
            ->willReturn([$optionMock]);
        $optionMock->expects($this->any())->method('getProductAttribute')->willReturn($productAttributeMock);
        $productAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('color');
        $simple->expects($this->any())->method('getData')->willReturn('color');
        $optionMock->expects($this->any())->method('getAttributeId')->willReturn('1');
        $optionMock->expects($this->any())->method('getPosition')->willReturn('0');

        $optionsFactoryMock->expects($this->any())->method('create')->willReturn([$optionMock]);
        $attributeFactoryMock->expects($this->any())->method('create')->willReturn($attributeMock);
        $attributeMock->expects($this->any())->method('getCollection')->willReturn($attributeCollectionMock);
        $attributeCollectionMock->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $attributeCollectionMock->expects($this->any())->method('getItems')->willReturn([$attributeMock]);
        $attributeMock->expects($this->any())->method('getId')->willReturn(1);
        $attributeMock->expects($this->any())->method('getOptions')->willReturn([$attributeOptionMock]);
        $extensionAttributesMock->expects($this->any())->method('setConfigurableProductOptions');
        $extensionAttributesMock->expects($this->any())->method('setConfigurableProductLinks');
        $this->productRepository->expects($this->once())->method('save');
        $this->assertTrue($this->object->addChild($productSku, $childSku));
    }

    public function testAddChildStateException()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The product is already attached.');
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurable->expects($this->any())->method('getId')->willReturn(666);

        $simple = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $simple->expects($this->any())->method('getId')->willReturn(1);

        $this->productRepository->expects($this->at(0))->method('get')->with($productSku)->willReturn($configurable);
        $this->productRepository->expects($this->at(1))->method('get')->with($childSku)->willReturn($simple);

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->willReturn(
                [0 => [1, 2, 3]]
            );
        $configurable->expects($this->never())->method('save');
        $this->object->addChild($productSku, $childSku);
    }

    public function testRemoveChild()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->getMockBuilder(Product::class)
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();

        $productType = $this->getMockBuilder(Configurable::class)
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $product->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);
        $this->productRepository->expects($this->any())
            ->method('get')
            ->with($productSku)
            ->willReturn($product);

        $option = $this->getMockBuilder(Product::class)
            ->setMethods(['getSku', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->willReturn($childSku);
        $option->expects($this->any())->method('getId')->willReturn(10);

        $productType->expects($this->once())->method('getUsedProducts')
            ->willReturn([$option]);

        $extensionAttributesMock = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->setMethods(['setConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $this->productRepository->expects($this->once())->method('save');
        $this->assertTrue($this->object->removeChild($productSku, $childSku));
    }

    public function testRemoveChildForbidden()
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->getMockForAbstractClass(ProductInterface::class);

        $product->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->productRepository->expects($this->any())->method('get')->willReturn($product);
        $this->object->removeChild($productSku, $childSku);
    }

    public function testRemoveChildInvalidChildSku()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->getMockBuilder(Product::class)
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Configurable::TYPE_CODE);
        $productType = $this->getMockBuilder(Configurable::class)
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $this->productRepository->expects($this->any())->method('get')->willReturn($product);

        $option = $this->getMockBuilder(Product::class)
            ->setMethods(['getSku', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->willReturn($childSku . '_invalid');
        $option->expects($this->any())->method('getId')->willReturn(10);
        $productType->expects($this->once())->method('getUsedProducts')
            ->willReturn([$option]);

        $this->object->removeChild($productSku, $childSku);
    }
}
