<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableType;

    /**
     * @var LinkManagement
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelperMock;

    protected function setUp()
    {
        $this->productRepository = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productFactory = $this->createPartialMock(
            \Magento\Catalog\Api\Data\ProductInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableType =
            $this->getMockBuilder(\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class)
                ->disableOriginalConstructor()->getMock();

        $this->object = $this->objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Model\LinkManagement::class,
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

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        )->disableOriginalConstructor()->getMock();

        $product->expects($this->any())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $product->expects($this->any())->method('getStoreId')->willReturn(1);
        $product->expects($this->any())->method('getTypeInstance')->willReturn($productTypeInstance);
        $productTypeInstance->expects($this->once())->method('setStoreFilter')->with(1, $product);

        $childProduct = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance->expects($this->any())->method('getUsedProducts')
            ->with($product)->willReturn([$childProduct]);

        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->willReturn($product);

        $attribute = $this->createMock(\Magento\Eav\Api\Data\AttributeInterface::class);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn('code');
        $childProduct->expects($this->once())->method('getDataUsingMethod')->with('code')->willReturn(false);
        $childProduct->expects($this->once())->method('getData')->with('code')->willReturn(10);
        $childProduct->expects($this->once())->method('getStoreId')->willReturn(1);
        $childProduct->expects($this->once())->method('getAttributes')->willReturn([$attribute]);

        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($productMock, ['store_id' => 1, 'code' => 10], \Magento\Catalog\Api\Data\ProductInterface::class)
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
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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

        $configurable = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getExtensionAttributes'])
            ->getMock();
        $simple = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getData'])
            ->getMock();

        $extensionAttributesMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtension::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getConfigurableProductOptions', 'setConfigurableProductOptions', 'setConfigurableProductLinks'
            ])
            ->getMock();
        $optionMock = $this->getMockBuilder(\Magento\ConfigurableProduct\Api\Data\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', 'getAttributeId'])
            ->getMock();
        $productAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $optionsFactoryMock = $this->getMockBuilder(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $reflectionClass = new \ReflectionClass(\Magento\ConfigurableProduct\Model\LinkManagement::class);
        $optionsFactoryReflectionProperty = $reflectionClass->getProperty('optionsFactory');
        $optionsFactoryReflectionProperty->setAccessible(true);
        $optionsFactoryReflectionProperty->setValue($this->object, $optionsFactoryMock);

        $attributeFactoryMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $attributeFactoryReflectionProperty = $reflectionClass->getProperty('attributeFactory');
        $attributeFactoryReflectionProperty->setAccessible(true);
        $attributeFactoryReflectionProperty->setValue($this->object, $attributeFactoryMock);

        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection', 'getOptions', 'getId', 'getAttributeCode', 'getStoreLabel'])
            ->getMock();
        $attributeOptionMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getLabel'])
            ->getMock();

        $attributeCollectionMock = $this->getMockBuilder(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'getItems'])
            ->getMock();

        $this->productRepository->expects($this->at(0))->method('get')->with($productSku)->willReturn($configurable);
        $this->productRepository->expects($this->at(1))->method('get')->with($childSku)->willReturn($simple);

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->will(
                $this->returnValue([0 => [1, 2, 3]])
            );

        $configurable->expects($this->any())->method('getId')->will($this->returnValue(666));
        $simple->expects($this->any())->method('getId')->will($this->returnValue(999));

        $configurable->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $extensionAttributesMock->expects($this->any())
            ->method('getConfigurableProductOptions')
            ->willReturn([$optionMock]);
        $optionMock->expects($this->any())->method('getProductAttribute')->willReturn($productAttributeMock);
        $productAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn('color');
        $simple->expects($this->any())->method('getData')->willReturn('color');
        $optionMock->expects($this->any())->method('getAttributeId')->willReturn('1');

        $optionsFactoryMock->expects($this->any())->method('create')->willReturn([$optionMock]);
        $attributeFactoryMock->expects($this->any())->method('create')->willReturn($attributeMock);
        $attributeMock->expects($this->any())->method('getCollection')->willReturn($attributeCollectionMock);
        $attributeCollectionMock->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $attributeCollectionMock->expects($this->any())->method('getItems')->willReturn([$attributeMock]);

        $attributeMock->expects($this->any())->method('getOptions')->willReturn([$attributeOptionMock]);

        $extensionAttributesMock->expects($this->any())->method('setConfigurableProductOptions');
        $extensionAttributesMock->expects($this->any())->method('setConfigurableProductLinks');

        $this->productRepository->expects($this->once())->method('save');

        $this->assertTrue(true, $this->object->addChild($productSku, $childSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage The product is already attached.
     */
    public function testAddChildStateException()
    {
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configurable->expects($this->any())->method('getId')->will($this->returnValue(666));

        $simple = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $simple->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->productRepository->expects($this->at(0))->method('get')->with($productSku)->willReturn($configurable);
        $this->productRepository->expects($this->at(1))->method('get')->with($childSku)->willReturn($simple);

        $this->configurableType->expects($this->once())->method('getChildrenIds')->with(666)
            ->will(
                $this->returnValue([0 => [1, 2, 3]])
            );
        $configurable->expects($this->never())->method('save');
        $this->object->addChild($productSku, $childSku);
    }

    public function testRemoveChild()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData', '__wakeup', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();

        $productType = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE));
        $this->productRepository->expects($this->any())
            ->method('get')
            ->with($productSku)
            ->will($this->returnValue($product));

        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSku', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $option->expects($this->any())->method('getId')->will($this->returnValue(10));

        $productType->expects($this->once())->method('getUsedProducts')
            ->will($this->returnValue([$option]));

        $extensionAttributesMock = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesInterface::class)
            ->setMethods(['setConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $this->productRepository->expects($this->once())->method('save');
        $this->assertTrue($this->object->removeChild($productSku, $childSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testRemoveChildForbidden()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);

        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($product));
        $this->object->removeChild($productSku, $childSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveChildInvalidChildSku()
    {
        $productSku = 'configurable';
        $childSku = 'simple_10';

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE));
        $productType = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class)
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($product));

        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getSku', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->will($this->returnValue($childSku . '_invalid'));
        $option->expects($this->any())->method('getId')->will($this->returnValue(10));
        $productType->expects($this->once())->method('getUsedProducts')
            ->will($this->returnValue([$option]));

        $this->object->removeChild($productSku, $childSku);
    }
}
