<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    protected $priceDataMock;

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
        $this->configurableTypeFactoryMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory'
        )->disableOriginalConstructor()->getMock();
        $this->priceDataMock = $this->getMockBuilder(
            '\Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price\Data'
        )->disableOriginalConstructor()->getMock();
        $this->productInterfaceMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getExtensionAttributes', 'getTypeId', 'getSku', 'getStoreId', 'getId'],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return $this->productMock;
        };
        $this->plugin = new AroundProductRepositorySave(
            $this->productOptionRepositoryMock,
            $this->priceDataMock,
            $this->configurableTypeFactoryMock
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

    public function testAroundSaveWhenProductIsConfigurableWithoutOptions()
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

        $this->priceDataMock->expects($this->never())
            ->method('setProductPrice');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productInterfaceMock)
        );
    }

    public function testAroundSaveWhenProductIsConfigurableWithLinks()
    {
        $links = [4, 5];
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
            '\Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable'
        )->disableOriginalConstructor()->getMock();
        $this->configurableTypeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($configurableTypeMock);
        $configurableTypeMock->expects($this->once())
            ->method('saveProducts')
            ->with($this->productMock, $links);

        $productId = 3;
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->priceDataMock->expects($this->once())
            ->method('setProductPrice')
            ->with($productId, null);

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

    public function testAroundSaveWhenProductIsConfigurableWithOptions()
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

        $productId = 3;
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->priceDataMock->expects($this->once())
            ->method('setProductPrice')
            ->with($productId, null);

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
