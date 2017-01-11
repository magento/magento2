<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Builder;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder\Plugin
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableTypeMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontendAttrMock;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->productFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->configurableTypeMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            [],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $methods = ['setTypeId', 'getAttributes', 'addData', 'setWebsiteIds', '__wakeup'];
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, $methods, [], '', false);
        $attributeMethods = [
            'getId',
            'getFrontend',
            'getAttributeCode',
            '__wakeup',
            'setIsRequired',
            'getIsUnique',
        ];
        $this->attributeMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            $attributeMethods,
            [],
            '',
            false
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
        $this->configurableMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            $configMethods,
            [],
            '',
            false
        );
        $this->frontendAttrMock = $this->getMock(
            \Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend::class,
            [],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            \Magento\Catalog\Controller\Adminhtml\Product\Builder::class,
            [],
            [],
            '',
            false
        );
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
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->will($this->returnValue(true));
        $valueMap = [
            ['attributes', null, ['attributes']],
            ['popup', null, true],
            ['required', null, '1,2'],
            ['product', null, 'product'],
            ['id', false, false],
            ['type', null, 'store_type'],
        ];
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($valueMap));
        $this->productMock->expects(
            $this->once()
        )->method(
            'setTypeId'
        )->with(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        )->will(
            $this->returnSelf()
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getAttributes'
        )->will(
            $this->returnValue([$this->attributeMock])
        );
        $this->attributeMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $this->attributeMock->expects($this->once())->method('setIsRequired')->with(1)->will($this->returnSelf());
        $this->productFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->configurableMock)
        );
        $this->configurableMock->expects($this->once())->method('setStoreId')->with(0)->will($this->returnSelf());
        $this->configurableMock->expects($this->once())->method('load')->with('product')->will($this->returnSelf());
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'setTypeId'
        )->with(
            'store_type'
        )->will(
            $this->returnSelf()
        );
        $this->configurableMock->expects($this->once())->method('getTypeInstance')->will($this->returnSelf());
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getSetAttributes'
        )->with(
            $this->configurableMock
        )->will(
            $this->returnValue([$this->attributeMock])
        );
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getIdFieldName'
        )->will(
            $this->returnValue('fieldName')
        );
        $this->attributeMock->expects($this->once())->method('getIsUnique')->will($this->returnValue(false));
        $this->attributeMock->expects(
            $this->once()
        )->method(
            'getFrontend'
        )->will(
            $this->returnValue($this->frontendAttrMock)
        );
        $this->frontendAttrMock->expects($this->once())->method('getInputType');
        $attributeCode = 'attribute_code';
        $this->attributeMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue($attributeCode)
        );
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $attributeCode
        )->will(
            $this->returnValue('attribute_data')
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'addData'
        )->with(
            [$attributeCode => 'attribute_data']
        )->will(
            $this->returnSelf()
        );
        $this->configurableMock->expects(
            $this->once()
        )->method(
            'getWebsiteIds'
        )->will(
            $this->returnValue('website_id')
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'setWebsiteIds'
        )->with(
            'website_id'
        )->will(
            $this->returnSelf()
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
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->will($this->returnValue(true));
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($valueMap));
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
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->will($this->returnValue(false));
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($valueMap));
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
