<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Builder;

use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder\Plugin;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Request\Http;
use Magento\Quote\Model\ResourceModel\Quote\Address\Attribute\Frontend;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $productFactoryMock;

    /**
     * @var MockObject
     */
    protected $configurableTypeMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $attributeMock;

    /**
     * @var MockObject
     */
    protected $configurableMock;

    /**
     * @var MockObject
     */
    protected $frontendAttrMock;

    /**
     * @var Builder|MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->configurableTypeMock = $this->createMock(
            Configurable::class
        );
        $this->requestMock = $this->createMock(Http::class);
        $methods = ['setTypeId', 'getAttributes', 'addData', 'setWebsiteIds', '__wakeup'];
        $this->productMock = $this->createPartialMock(Product::class, $methods);
        $attributeMethods = [
            'getId',
            'getFrontend',
            'getAttributeCode',
            '__wakeup',
            'setIsRequired',
            'getIsUnique',
        ];
        $this->attributeMock = $this->createPartialMock(
            Attribute::class,
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
            Configurable::class,
            $configMethods
        );
        $this->frontendAttrMock = $this->createMock(
            Frontend::class
        );
        $this->subjectMock = $this->createMock(Builder::class);
        $this->plugin = new Plugin(
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
            Configurable::TYPE_CODE
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
            Type::TYPE_SIMPLE
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
