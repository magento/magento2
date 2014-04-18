<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', array('create'));
        $this->configurableTypeMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            array(),
            array(),
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $methods = array('setTypeId', 'getAttributes', 'addData', 'setWebsiteIds', '__wakeup');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, array(), '', false);
        $product = $this->productMock;
        $attributeMethods = array(
            'getId',
            'getFrontend',
            'getAttributeCode',
            '__wakeup',
            'setIsRequired',
            'getIsUnique'
        );
        $this->attributeMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            $attributeMethods,
            array(),
            '',
            false
        );
        $configMethods = array(
            'setStoreId',
            'getTypeInstance',
            'getIdFieldName',
            'getData',
            'getWebsiteIds',
            '__wakeup',
            'load',
            'setTypeId',
            'getEditableAttributes'
        );
        $this->configurableMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            $configMethods,
            array(),
            '',
            false
        );
        $this->frontendAttrMock = $this->getMock(
            'Magento\Sales\Model\Resource\Quote\Address\Attribute\Frontend',
            array(),
            array(),
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Builder',
            array(),
            array(),
            '',
            false
        );
        $this->closureMock = function () use ($product) {
            return $product;
        };
        $this->plugin = new \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder\Plugin(
            $this->productFactoryMock,
            $this->configurableTypeMock
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAroundBuild()
    {
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->will($this->returnValue(true));
        $valueMap = array(
            array('attributes', null, array('attributes')),
            array('popup', null, true),
            array('required', null, '1,2'),
            array('product', null, 'product'),
            array('id', false, false),
            array('type', null, 'store_type')
        );
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
        $this->configurableTypeMock->expects(
            $this->once()
        )->method(
            'setUsedProductAttributeIds'
        )->with(
            array('attributes')
        )->will(
            $this->returnSelf()
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getAttributes'
        )->will(
            $this->returnValue(array($this->attributeMock))
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
            'getEditableAttributes'
        )->with(
            $this->configurableMock
        )->will(
            $this->returnValue(array($this->attributeMock))
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
            array($attributeCode => 'attribute_data')
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
            $this->plugin->aroundBuild($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    public function testAroundBuildWhenProductNotHaveAttributeAndRequiredParameters()
    {
        $valueMap = array(
            array('attributes', null, null),
            array('popup', null, false),
            array('product', null, 'product'),
            array('id', false, false)
        );
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
            $this->plugin->aroundBuild($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }

    public function testAroundBuildWhenAttributesAreEmpty()
    {
        $valueMap = array(array('popup', null, false), array('product', null, 'product'), array('id', false, false));
        $this->requestMock->expects($this->once())->method('has')->with('attributes')->will($this->returnValue(false));
        $this->requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($valueMap));
        $this->productMock->expects($this->never())->method('setTypeId');
        $this->productMock->expects($this->never())->method('getAttributes');
        $this->productFactoryMock->expects($this->never())->method('create');
        $this->configurableMock->expects($this->never())->method('getTypeInstance');
        $this->attributeMock->expects($this->never())->method('getAttributeCode');
        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundBuild($this->subjectMock, $this->closureMock, $this->requestMock)
        );
    }
}
