<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Order\Admin\Item\Plugin;

class ConfigurableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin\Configurable
     */
    protected $configurable;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->itemMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getProductType', 'getProductOptions', '__wakeup']
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->productFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->subjectMock = $this->createMock(\Magento\Sales\Model\Order\Admin\Item::class);
        $this->configurable = new \Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin\Configurable(
            $this->productFactoryMock
        );
    }

    public function testAroundGetNameIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue(['simple_name' => 'simpleName'])
        );
        $this->assertEquals(
            'simpleName',
            $this->configurable->aroundGetName($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetNameIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->will($this->returnValue('simple'));
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetName($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetSkuIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue(['simple_sku' => 'simpleName'])
        );
        $this->assertEquals(
            'simpleName',
            $this->configurable->aroundGetSku($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetSkuIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->will($this->returnValue('simple'));
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetSku($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetProductIdIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue(['simple_sku' => 'simpleName'])
        );
        $this->productFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->productMock)
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getIdBySku'
        )->with(
            'simpleName'
        )->will(
            $this->returnValue('id')
        );
        $this->assertEquals(
            'id',
            $this->configurable->aroundGetProductId($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetProductIdIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->will($this->returnValue('simple'));
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetProductId($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
