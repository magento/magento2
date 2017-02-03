<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\ConfigurableProduct\Model\Plugin\AroundProductRepositorySave;

class AroundProductRepositorySaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AroundProductRepositorySave
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableTypeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productInterfaceMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productOptionRepositoryMock = $this->getMock(
            'Magento\ConfigurableProduct\Api\OptionRepositoryInterface'
        );
        $this->configurableTypeFactoryMock = $this->getMock(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productInterfaceMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getExtensionAttributes', 'getTypeId', 'getSku', 'getStoreId', 'getId', 'getTypeInstance'],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return $this->productMock;
        };

        $this->productFactoryMock = $this->getMockBuilder('\Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            'Magento\ConfigurableProduct\Model\Plugin\AroundProductRepositorySave',
            [
                'optionRepository' => $this->productOptionRepositoryMock,
                'productFactory' => $this->productFactoryMock,
                'typeConfigurableFactory' => $this->configurableTypeFactoryMock
            ]
        );

        $this->productExtensionMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductExtension',
            [
                'getConfigurableProductOptions',
                'getConfigurableProductLinks',
                'setConfigurableProductOptions',
                'setConfigurableProductLinks',
            ],
            [],
            '',
            false
        );
    }

    public function testAroundSaveWhenProductIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productMock->expects($this->never())->method('getExtensionAttributes');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWithoutOptions()
    {
        $this->productInterfaceMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->productInterfaceMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn(null);

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productInterfaceMock)
        );
    }

    protected function setupProducts($productIds, $attributeCode, $additionalProductId = null)
    {
        $count = 0;
        $products = [];
        foreach ($productIds as $productId) {
            $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
                ->disableOriginalConstructor()
                ->getMock();
            $productMock->expects($this->once())
                ->method('load')
                ->with($productId)
                ->willReturnSelf();
            $productMock->expects($this->once())
                ->method('getId')
                ->willReturn($productId);
            $productMock->expects($this->any())
                ->method('getData')
                ->with($attributeCode)
                ->willReturn($productId);
            $this->productFactoryMock->expects($this->at($count))
                ->method('create')
                ->willReturn($productMock);
            $products[] = $productMock;
            $count++;
        }

        if ($additionalProductId) {
            $nonExistingProductMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
                ->disableOriginalConstructor()
                ->getMock();
            $nonExistingProductMock->expects($this->once())
                ->method('load')
                ->with($additionalProductId)
                ->willReturnSelf();
            $this->productFactoryMock->expects($this->at($count))
                ->method('create')
                ->willReturn($nonExistingProductMock);
            $products[] = $nonExistingProductMock;
        }
        return $products;
    }

    protected function setupConfigurableProductAttributes($attributeCodes)
    {
        $configurableProductTypeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();

        $this->productMock->expects($this->atLeastOnce())
            ->method('getTypeInstance')
            ->willReturn($configurableProductTypeMock);

        $configurableAttributes = [];
        foreach ($attributeCodes as $attributeCode) {
            $configurableAttribute = $this->getMockBuilder(
                '\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute'
            )->setMethods(['getProductAttribute'])
                ->disableOriginalConstructor()
                ->getMock();
            $productAttributeMock = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Eav\Attribute')
                ->disableOriginalConstructor()
                ->getMock();
            $productAttributeMock->expects($this->once())
                ->method('getAttributeCode')
                ->willReturn($attributeCode);
            $configurableAttribute->expects($this->once())
                ->method('getProductAttribute')
                ->willReturn($productAttributeMock);
            $configurableAttributes[] = $configurableAttribute;
        }

        $configurableProductTypeMock->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->productMock)
            ->willReturn($configurableAttributes);

        return $this;
    }

    public function testAroundSaveWithLinks()
    {
        $links = [4, 5];
        $configurableAttributeCode = 'color';
        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->setupConfigurableProductAttributes([$configurableAttributeCode]);
        $this->setupProducts($links, $configurableAttributeCode);

        $configurableTypeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();
        $this->configurableTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->once())
            ->method('saveProducts')
            ->with($this->productMock, $links);

        $productSku = 'configurable-sku';
        $this->productMock->expects($this->any())
            ->method('getSku')
            ->willReturn($productSku);
        $newProductMock = $this->setupReload($productSku);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Product with id "6" does not exist.
     */
    public function testAroundSaveWithNonExistingLinks()
    {
        $links = [4, 5];
        $nonExistingId = 6;
        $configurableAttributeCode = 'color';

        $this->setupConfigurableProductAttributes([$configurableAttributeCode]);
        $productMocks = $this->setupProducts($links, $configurableAttributeCode, $nonExistingId);
        $nonExistingProductMock = $productMocks[2];
        $nonExistingProductMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $links[] = $nonExistingId;

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $configurableTypeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();
        $this->configurableTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->never())
            ->method('saveProducts')
            ->with($this->productMock, $links);

        $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Product with id "6" does not contain required attribute "color".
     */
    public function testAroundSaveWithLinksWithMissingAttribute()
    {
        $links = [4, 5];
        $simpleProductId = 6;
        $configurableAttributeCode = 'color';

        $this->setupConfigurableProductAttributes([$configurableAttributeCode]);
        $productMocks = $this->setupProducts($links, $configurableAttributeCode, $simpleProductId);
        $simpleProductMock = $productMocks[2];
        $simpleProductMock->expects($this->once())
            ->method('getId')
            ->willReturn($simpleProductId);
        $simpleProductMock->expects($this->any())
            ->method('getData')
            ->with($configurableAttributeCode)
            ->willReturn(null);

        $links[] = $simpleProductId;

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $configurableTypeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();
        $this->configurableTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->never())
            ->method('saveProducts')
            ->with($this->productMock, $links);

        $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Products "6" and 4 have the same set of attribute values.
     */
    public function testAroundSaveWithLinksWithDuplicateAttributes()
    {
        $links = [4, 5];
        $simpleProductId = 6;
        $configurableAttributeCode = 'color';

        $this->setupConfigurableProductAttributes([$configurableAttributeCode]);
        $productMocks = $this->setupProducts($links, $configurableAttributeCode, $simpleProductId);
        $simpleProductMock = $productMocks[2];
        $simpleProductMock->expects($this->once())
            ->method('getId')
            ->willReturn($simpleProductId);
        $simpleProductMock->expects($this->any())
            ->method('getData')
            ->with($configurableAttributeCode)
            ->willReturn(4);

        $links[] = $simpleProductId;

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $configurableTypeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();
        $this->configurableTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->never())
            ->method('saveProducts')
            ->with($this->productMock, $links);

        $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage The configurable product does not have any variation attribute.
     */
    public function testAroundSaveWithLinksWithoutVariationAttributes()
    {
        $links = [4, 5];

        $this->setupConfigurableProductAttributes([]);

        $this->productMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductOptions')
            ->willReturn(null);
        $this->productExtensionMock->expects($this->once())
            ->method('getConfigurableProductLinks')
            ->willReturn($links);

        $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock);
    }

    public function testAroundSaveWithOptions()
    {
        $productSku = "configurable_sku";
        $this->productInterfaceMock->expects($this->once())->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
        //two options with id 5 and 6
        $options = $this->setupOptions([5, 6]);
        //two existing options with id 4 and 5
        $this->setupExistingOptions([4, 5]);

        $this->productMock->expects($this->any())->method('getSku')
            ->will($this->returnValue($productSku));

        $this->productOptionRepositoryMock->expects($this->at(0))
            ->method('save')
            ->with($productSku, $options[0]);
        $this->productOptionRepositoryMock->expects($this->at(1))
            ->method('save')
            ->with($productSku, $options[1]);
        $this->productOptionRepositoryMock->expects($this->at(2))
            ->method('deleteById')
            ->with($productSku, 4);

        $configurableProductTypeMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();
        $configurableProductTypeMock->expects($this->once())
            ->method('resetConfigurableAttributes')
            ->with($this->productMock)
            ->willReturnSelf();
        $this->productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($configurableProductTypeMock);

        $newProductMock = $this->setupReload($productSku);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productInterfaceMock)
        );
    }

    protected function setupReload($productSku)
    {
        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, false, null, true)
            ->willReturn($newProductMock);
        return $newProductMock;
    }

    protected function setupExistingOptions(array $existingOptionIds)
    {
        $options = [];
        foreach ($existingOptionIds as $existingOptionId) {
            $optionMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionInterface');
            $optionMock->expects($this->any())
                ->method('getId')
                ->willReturn($existingOptionId);
            $options[] = $optionMock;
        }

        $productExtensionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductExtension')
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableProductOptions'])
            ->getMock();
        $productExtensionMock->expects($this->any())
            ->method('getConfigurableProductOptions')
            ->willReturn($options);

        $this->productMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);
    }

    protected function setupOptions(array $optionIds)
    {
        $options = [];
        foreach ($optionIds as $optionId) {
            $optionMock = $this->getMock('\Magento\ConfigurableProduct\Api\Data\OptionInterface');
            $optionMock->expects($this->any())
                ->method('getId')
                ->willReturn($optionId);
            $options[] = $optionMock;
        }

        $productExtensionMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductExtension')
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->getMock();
        $productExtensionMock->expects($this->any())
            ->method('getConfigurableProductOptions')
            ->willReturn($options);
        $productExtensionMock->expects($this->any())
            ->method('getConfigurableProductLinks')
            ->willReturn(null);

        $this->productInterfaceMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($productExtensionMock);
        return $options;
    }
}
