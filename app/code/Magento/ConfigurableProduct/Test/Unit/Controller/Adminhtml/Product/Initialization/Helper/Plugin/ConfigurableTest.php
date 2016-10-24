<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;

class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Configurable
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $variationHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->productTypeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );
        $this->variationHandler = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\VariationHandler',
            [],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $methods = [
            'setNewVariationsAttributeSetId',
            'setAssociatedProductIds',
            'setCanSaveConfigurableAttributes',
            'getTypeId',
            '__wakeup',
        ];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, [], '', false);
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );
        $this->plugin = new Configurable($this->variationHandler, $this->productTypeMock, $this->requestMock);
    }

    public function testAfterInitializeIfAttributesNotEmptyAndActionNameNotGenerateVariations()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn(ConfigurableProduct::TYPE_CODE);
        $associatedProductIds = ['key' => 'value'];
        $generatedProductIds = ['key_one' => 'value_one'];
        $expectedArray = ['key' => 'value', 'key_one' => 'value_one'];
        $attributes = ['key' => 'value'];
        $postValue = 'postValue';
        $postValueMap = [
            ['new-variations-attribute-set-id', null, $postValue],
            ['associated_product_ids', [], $associatedProductIds],
            ['affect_configurable_product_attributes', null, $postValue],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($postValueMap));

        $paramValueMap = [
            ['variations-matrix', [], $postValue],
            ['attributes', null, $attributes],
        ];
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($paramValueMap));
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'setUsedProductAttributeIds'
        )->with(
            $attributes,
            $this->productMock
        );
        $this->productMock->expects($this->once())->method('setNewVariationsAttributeSetId')->with($postValue);
        $this->variationHandler->expects(
            $this->once()
        )->method(
            'generateSimpleProducts'
        )->with(
            $this->productMock,
            $postValue
        )->will(
            $this->returnValue($generatedProductIds)
        );
        $this->productMock->expects($this->once())->method('setAssociatedProductIds')->with($expectedArray);
        $this->productMock->expects($this->once())->method('setCanSaveConfigurableAttributes')->with(true);
        $this->plugin->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeIfAttributesNotEmptyAndActionNameGenerateVariations()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn(ConfigurableProduct::TYPE_CODE);
        $associatedProductIds = ['key' => 'value'];
        $attributes = ['key' => 'value'];
        $postValue = 'postValue';
        $valueMap = [
            ['new-variations-attribute-set-id', null, $postValue],
            ['associated_product_ids', [], $associatedProductIds],
            ['affect_configurable_product_attributes', null, $postValue],
        ];
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($valueMap));
        $paramValueMap = [
            ['variations-matrix', [], []],
            ['attributes', null, $attributes],
        ];
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($paramValueMap));
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'setUsedProductAttributeIds'
        )->with(
            $attributes,
            $this->productMock
        );
        $this->productMock->expects($this->once())->method('setNewVariationsAttributeSetId')->with($postValue);
        $this->productTypeMock->expects($this->never())->method('generateSimpleProducts');
        $this->productMock->expects($this->once())->method('setAssociatedProductIds')->with($associatedProductIds);
        $this->productMock->expects($this->once())->method('setCanSaveConfigurableAttributes')->with(true);
        $this->plugin->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeIfAttributesEmpty()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn(ConfigurableProduct::TYPE_CODE);
        $this->requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'attributes'
        )->will(
            $this->returnValue([])
        );
        $this->productTypeMock->expects($this->never())->method('setUsedProductAttributeIds');
        $this->requestMock->expects($this->never())->method('getPost');
        $this->productTypeMock->expects($this->never())->method('generateSimpleProducts');
        $this->plugin->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeForNotConfigurableProduct()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('non-configurable');
        $this->productTypeMock->expects($this->never())->method('setUsedProductAttributeIds');
        $this->requestMock->expects($this->never())->method('getPost');
        $this->productTypeMock->expects($this->never())->method('generateSimpleProducts');
        $this->plugin->afterInitialize($this->subjectMock, $this->productMock);
    }
}
