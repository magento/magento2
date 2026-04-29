<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute as ConfigurableAttribute;
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
    use MockCreationTrait;

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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->objectManagerHelper = new ObjectManager($this);
        $this->productFactory = $this->createPartialMock(ProductInterfaceFactory::class, ['create']);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);

        $this->configurableType = $this->createMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class
        );

        $this->object = $this->objectManagerHelper->getObject(
            LinkManagement::class,
            [
                'productRepository' => $this->productRepository,
                'productFactory' => $this->productFactory,
                'configurableType' => $this->configurableType,
                'dataObjectHelper' => $this->dataObjectHelperMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetChildren(): void
    {
        $productId = 'test';

        $product = $this->createMock(Product::class);

        $productTypeInstance = $this->createMock(Configurable::class);

        $product->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $product->method('getStoreId')->willReturn(1);
        $product->method('getTypeInstance')->willReturn($productTypeInstance);
        $productTypeInstance->expects($this->once())->method('setStoreFilter')->with(1, $product);

        $childProduct = $this->createMock(Product::class);

        $productTypeInstance->expects($this->any())->method('getUsedProducts')
            ->with($product)->willReturn([$childProduct]);

        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->willReturn($product);

        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn('code');
        $childProduct->expects($this->once())->method('getDataUsingMethod')->with('code')->willReturn(false);
        $childProduct->expects($this->once())->method('getData')->with('code')->willReturn(10);
        $childProduct->expects($this->once())->method('getStoreId')->willReturn(1);
        $childProduct->expects($this->once())->method('getAttributes')->willReturn([$attribute]);
        $childProduct->expects($this->once())->method('getMediaGalleryEntries')->willReturn([]);

        $productMock = $this->createMock(ProductInterface::class);
        $productMock->expects($this->once())->method('setMediaGalleryEntries')->with([])->willReturnSelf();

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $productMock,
                ['store_id' => 1, 'code' => 10],
                ProductInterface::class
            )->willReturnSelf();

        $this->productFactory->expects($this->once())
            ->method('create')
            ->willReturn($productMock);

        $products = $this->object->getChildren($productId);
        $this->assertCount(1, $products);
        $this->assertEquals($productMock, $products[0]);
    }

    /**
     * @return void
     */
    public function testGetWithNonConfigurableProduct(): void
    {
        $productId= 'test';
        $product = $this->createMock(Product::class);
        $product->method('getTypeId')->willReturn('simple');
        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->willReturn($product);

        $this->assertEmpty($this->object->getChildren($productId));
    }

    /**
     * @return void
     */
    public function testAddChild(): void
    {
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->createPartialMock(Product::class, ['getId', 'getExtensionAttributes']);
        $simple = $this->createPartialMock(Product::class, ['getId', 'getData']);
        $extensionAttributesMock = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getConfigurableProductOptions', 'setConfigurableProductOptions', 'setConfigurableProductLinks']
        );
        $productAttributeMock = $this->createPartialMock(AbstractAttribute::class, ['getAttributeCode']);
        $optionMock = $this->createPartialMock(ConfigurableAttribute::class, []);
        $optionMock->setProductAttribute($productAttributeMock);
        $optionMock->setAttributeId(1);
        $optionMock->setPosition(1);
        $optionsFactoryMock = $this->createPartialMock(Factory::class, ['create']);
        $reflectionClass = new \ReflectionClass(LinkManagement::class);
        $optionsFactoryReflectionProperty = $reflectionClass->getProperty('optionsFactory');
        $optionsFactoryReflectionProperty->setValue($this->object, $optionsFactoryMock);

        $attributeFactoryMock = $this->createPartialMock(AttributeFactory::class, ['create']);
        $attributeFactoryReflectionProperty = $reflectionClass->getProperty('attributeFactory');
        $attributeFactoryReflectionProperty->setValue($this->object, $attributeFactoryMock);

        $attributeMock = $this->createPartialMock(
            Attribute::class,
            ['getCollection', 'getOptions', 'getId', 'getAttributeCode', 'getStoreLabel']
        );
        $attributeOptionMock = $this->createPartialMock(Option::class, ['getValue', 'getLabel']);
        $attributeCollectionMock = $this->createPartialMock(Collection::class, ['addFieldToFilter', 'getItems']);

        $this->productRepository
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$productSku] => $configurable,
                [$childSku] => $simple
            });

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->willReturn(
                [0 => [1, 2, 3]]
            );

        $configurable->method('getId')->willReturn(666);
        $simple->method('getId')->willReturn(999);

        $configurable->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $extensionAttributesMock->method('getConfigurableProductOptions')->willReturn([$optionMock]);
        $productAttributeMock->method('getAttributeCode')->willReturn('color');
        $simple->method('getData')->willReturn('color');

        $optionsFactoryMock->method('create')->willReturn([$optionMock]);
        $attributeFactoryMock->method('create')->willReturn($attributeMock);
        $attributeMock->method('getCollection')->willReturn($attributeCollectionMock);
        $attributeCollectionMock->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $attributeCollectionMock->method('getItems')->willReturn([$attributeMock]);
        $attributeMock->method('getId')->willReturn(1);
        $attributeMock->method('getOptions')->willReturn([$attributeOptionMock]);
        // Helper methods return $this by default
        $this->productRepository->expects($this->once())->method('save');
        $this->assertTrue($this->object->addChild($productSku, $childSku));
    }

    /**
     * @return void
     */
    public function testAddChildStateException(): void
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The product is already attached.');
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->createMock(Product::class);

        $configurable->method('getId')->willReturn(666);

        $simple = $this->createMock(Product::class);

        $simple->method('getId')->willReturn(1);

        $this->productRepository
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$productSku] => $configurable,
                [$childSku] => $simple
            });

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->willReturn(
                [0 => [1, 2, 3]]
            );
        $configurable->expects($this->never())->method('save');
        $this->object->addChild($productSku, $childSku);
    }

    /**
     * @return void
     */
    public function testRemoveChild(): void
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->createPartialMock(
            Product::class,
            ['getTypeInstance', 'save', 'getTypeId', 'addData', 'getExtensionAttributes']
        );

        $productType = $this->createPartialMock(Configurable::class, ['getUsedProducts']);
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $product->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $this->productRepository->expects($this->any())
            ->method('get')
            ->with($productSku)
            ->willReturn($product);

        $option = $this->createPartialMock(Product::class, ['getSku', 'getId']);
        $option->method('getSku')->willReturn($childSku);
        $option->method('getId')->willReturn(10);

        $productType->expects($this->once())->method('getUsedProducts')
            ->willReturn([$option]);

        $extensionAttributesMock = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getConfigurableProductOptions', 'setConfigurableProductOptions', 'setConfigurableProductLinks']
        );

        $product->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $this->productRepository->expects($this->once())->method('save');
        $this->assertTrue($this->object->removeChild($productSku, $childSku));
    }

    /**
     * @return void
     */
    public function testRemoveChildForbidden(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->createMock(ProductInterface::class);

        $product->method('getTypeId')->willReturn(Type::TYPE_SIMPLE);
        $this->productRepository->method('get')->willReturn($product);
        $this->object->removeChild($productSku, $childSku);
    }

    /**
     * @return void
     */
    public function testRemoveChildInvalidChildSku(): void
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->createPartialMock(Product::class, ['getTypeInstance', 'save', 'getTypeId', 'addData']);
        $product->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $productType = $this->createPartialMock(Configurable::class, ['getUsedProducts']);
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $this->productRepository->method('get')->willReturn($product);

        $option = $this->createPartialMock(Product::class, ['getSku', 'getId']);
        $option->method('getSku')->willReturn($childSku . '_invalid');
        $option->method('getId')->willReturn(10);
        $productType->expects($this->once())->method('getUsedProducts')
            ->willReturn([$option]);

        $this->object->removeChild($productSku, $childSku);
    }
}
