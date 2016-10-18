<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model;

use Magento\ConfigurableProduct\Model\LinkManagement;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class LinkManagementTest extends \PHPUnit_Framework_TestCase
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
        $this->productRepository = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productFactory = $this->getMock(
            '\Magento\Catalog\Api\Data\ProductInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurableType =
            $this->getMockBuilder('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
                ->disableOriginalConstructor()->getMock();

        $this->object = $this->objectManagerHelper->getObject(
            '\Magento\ConfigurableProduct\Model\LinkManagement',
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

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->any())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $product->expects($this->any())->method('getStoreId')->willReturn(1);
        $product->expects($this->any())->method('getTypeInstance')->willReturn($productTypeInstance);
        $productTypeInstance->expects($this->once())->method('setStoreFilter')->with(1, $product);

        $childProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productTypeInstance->expects($this->any())->method('getUsedProducts')
            ->with($product)->willReturn([$childProduct]);

        $this->productRepository->expects($this->any())
            ->method('get')->with($productId)
            ->willReturn($product);

        $attribute = $this->getMock('\Magento\Eav\Api\Data\AttributeInterface');
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn('code');
        $childProduct->expects($this->once())->method('getDataUsingMethod')->with('code')->willReturn(false);
        $childProduct->expects($this->once())->method('getData')->with('code')->willReturn(10);
        $childProduct->expects($this->once())->method('getStoreId')->willReturn(1);
        $childProduct->expects($this->once())->method('getAttributes')->willReturn([$attribute]);

        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($productMock, ['store_id' => 1, 'code' => 10], '\Magento\Catalog\Api\Data\ProductInterface')
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
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
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

        $configurable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getExtensionAttributes', 'save'])
            ->getMock();
        $simple = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getData'])
            ->getMock();

        $extensionAttributesMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductExtension')
            ->disableOriginalConstructor()
            ->setMethods([
                'getConfigurableProductOptions', 'setConfigurableProductOptions', 'setConfigurableProductLinks'
            ])
            ->getMock();
        $optionMock = $this->getMockBuilder('Magento\ConfigurableProduct\Api\Data\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getProductAttribute', 'getAttributeId'])
            ->getMock();
        $productAttributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $optionsFactoryMock = $this->getMockBuilder('Magento\ConfigurableProduct\Helper\Product\Options\Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $reflectionClass = new \ReflectionClass('Magento\ConfigurableProduct\Model\LinkManagement');
        $optionsFactoryReflectionProperty = $reflectionClass->getProperty('optionsFactory');
        $optionsFactoryReflectionProperty->setAccessible(true);
        $optionsFactoryReflectionProperty->setValue($this->object, $optionsFactoryMock);

        $attributeFactoryMock = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $attributeFactoryReflectionProperty = $reflectionClass->getProperty('attributeFactory');
        $attributeFactoryReflectionProperty->setAccessible(true);
        $attributeFactoryReflectionProperty->setValue($this->object, $attributeFactoryMock);

        $attributeMock = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getCollection', 'getOptions', 'getId', 'getAttributeCode', 'getStoreLabel'])
            ->getMock();
        $attributeOptionMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'getLabel'])
            ->getMock();

        $attributeCollectionMock = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection'
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
     * @expectedExceptionMessage Product has been already attached
     */
    public function testAddChildStateException()
    {
        $productSku = 'configurable-sku';
        $childSku = 'simple-sku';

        $configurable = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $configurable->expects($this->any())->method('getId')->will($this->returnValue(666));

        $simple = $this->getMockBuilder('Magento\Catalog\Model\Product')
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

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData', '__wakeup', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();

        $productType = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
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

        $option = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->setMethods(['getSku', 'getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $option->expects($this->any())->method('getId')->will($this->returnValue(10));

        $productType->expects($this->once())->method('getUsedProducts')
            ->will($this->returnValue([$option]));

        $extensionAttributesMock = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setConfigurableProductLinks'])
            ->getMock();

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

        $product = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');

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

        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'save', 'getTypeId', 'addData', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE));
        $productType = $this->getMockBuilder('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->setMethods(['getUsedProducts'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->once())->method('getTypeInstance')->willReturn($productType);

        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($product));

        $option = $this->getMockBuilder('\Magento\Catalog\Model\Product')
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
