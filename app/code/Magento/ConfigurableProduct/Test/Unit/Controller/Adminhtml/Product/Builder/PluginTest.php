<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setWebsiteIds'])
            ->onlyMethods(['setTypeId', 'getAttributes', 'addData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMock = $this->createPartialMock(
            Attribute::class,
            [
                'getId',
                'getFrontend',
                'getAttributeCode',
                'setIsRequired',
                'getIsUnique',
            ]
        );
        $this->configurableMock = $this->getMockBuilder(Configurable::class)
            ->addMethods(['setStoreId', 'getTypeInstance', 'getIdFieldName', 'getData', 'getWebsiteIds', 'load'])
            ->onlyMethods(['setTypeId', 'getSetAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
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
            Configurable::TYPE_CODE
        )->willReturnSelf();
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
        )->willReturnSelf();
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
        )->willReturnSelf();
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
        )->willReturnSelf();

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
