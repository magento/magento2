<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Builder;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder\Plugin
     */
    protected $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurableTypeMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configurableMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendAttrMock;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->productFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->configurableTypeMock = $this->createMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class
        );
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $methods = ['setTypeId', 'getAttributes', 'addData', 'setWebsiteIds', '__wakeup'];
        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $methods);
        $attributeMethods = [
            'getId',
            'getFrontend',
            'getAttributeCode',
            '__wakeup',
            'setIsRequired',
            'getIsUnique',
        ];
        $this->attributeMock = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            $attributeMethods
        );
        $configMethods = [
            'setStoreId',
            'getTypeInstance',
            'getIdFieldName',
            'getData',
            'getWebsiteIds',
            '__wakeup',
            'load',
            'setTypeId',
            'getSetAttributes',
        ];
        $this->configurableMock = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            $configMethods
        );
        $this->frontendAttrMock = $this->createMock(
            \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend::class
        );
        $this->subjectMock = $this->createMock(\Magento\Catalog\Controller\Adminhtml\Product\Builder::class);
        $this->plugin = new \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder\Plugin(
            $this->productFactoryMock,
            $this->configurableTypeMock
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAfterBuild()
    {
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->willReturn(true);
        $valueMap = [
            ['attributes', null, ['attributes']],
            ['popup', null, true],
            ['required', null, '1,2'],
            ['product', null, 'product'],
            ['id', false, false],
            ['type', null, 'store_type'],
        ];
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap($valueMap);
        $this->productMock->expects(
            $this->once()
        )->method(
            'setTypeId'
        )->with(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        )->willReturnSelf(
            
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getAttributes'
        )->willReturn(
            [$this->attributeMock]
        );
        $this->attributeMock->expects($this->once())->method('getId')->willReturn(1);
        $this->attributeMock->expects($this->once())->method('setIsRequired')->with(1)->willReturnSelf();
        $this->productFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->configurableMock
        );
        $this->configurableMock->expects($this->once())->method('setStoreId')->with(0)->willReturnSelf();
        $this->configurableMock->expects($this->once())->method('load')->with('product')->willReturnSelf();
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'setTypeId'
        )->with(
            'store_type'
        )->willReturnSelf(
            
        );
        $this->configurableMock->expects($this->once())->method('getTypeInstance')->willReturnSelf();
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getSetAttributes'
        )->with(
            $this->configurableMock
        )->willReturn(
            [$this->attributeMock]
        );
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getIdFieldName'
        )->willReturn(
            'fieldName'
        );
        $this->attributeMock->expects($this->once())->method('getIsUnique')->willReturn(false);
        $this->attributeMock->expects(
            $this->once()
        )->method(
            'getFrontend'
        )->willReturn(
            $this->frontendAttrMock
        );
        $this->frontendAttrMock->expects($this->once())->method('getInputType');
        $attributeCode = 'attribute_code';
        $this->attributeMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->willReturn(
            $attributeCode
        );
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $attributeCode
        )->willReturn(
            'attribute_data'
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [$attributeCode => 'attribute_data']
        )->willReturnSelf(
            
        );
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getWebsiteIds'
        )->willReturn(
            'website_id'
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'setWebsiteIds'
        )->with(
            'website_id'
        )->willReturnSelf(
            
        );

        $this->assertEquals(
            $this->productMock,
            $this->plugin->afterBuild($this->subjectMock, $this->productMock, $this->requestMock)
        );
    }

    public function testAfterBuildWhenProductNotHaveAttributeAndRequiredParameters()
    {
        $valueMap = [
            ['attributes', null, null],
            ['popup', null, false],
            ['product', null, 'product'],
            ['id', false, false],
        ];
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->willReturn(true);
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap($valueMap);
        $this->productMock->expects(
            $this->once()
        )->method(
            'setTypeId'
        )->with(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        );
        $this->productMock->expects($this->never())->method('getAttributes');
        $this->productFactoryMock->expects($this->never())->method('create');
        $this->configurableMock->expects($this->never())->method('getTypeInstance');
        $this->attributeMock->expects($this->never())->method('getAttributeCode');
        $this->assertEquals(
            $this->productMock,
            $this->plugin->afterBuild($this->subjectMock, $this->productMock, $this->requestMock)
        );
    }

    public function testAfterBuildWhenAttributesAreEmpty()
    {
        $valueMap = [['popup', null, false], ['product', null, 'product'], ['id', false, false]];
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->willReturn(false);
        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap($valueMap);
        $this->productMock->expects($this->never())->method('setTypeId');
        $this->productMock->expects($this->never())->method('getAttributes');
        $this->productFactoryMock->expects($this->never())->method('create');
        $this->configurableMock->expects($this->never())->method('getTypeInstance');
        $this->attributeMock->expects($this->never())->method('getAttributeCode');
        $this->assertEquals(
            $this->productMock,
            $this->plugin->afterBuild($this->subjectMock, $this->productMock, $this->requestMock)
        );
    }
}
