<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Helper\Product\Configuration;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Helper\Product\Configuration\Plugin
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeInstanceMock;

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
        $this->itemMock = $this->createMock(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->typeInstanceMock = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            ['getSelectedAttributesInfo', '__wakeup']
        );
        $this->itemMock->expects($this->once())->method('getProduct')->will($this->returnValue($this->productMock));
        $this->closureMock = function () {
            return ['options'];
        };
        $this->subjectMock = $this->createMock(\Magento\Catalog\Helper\Product\Configuration::class);
        $this->plugin = new \Magento\ConfigurableProduct\Helper\Product\Configuration\Plugin();
    }

    public function testAroundGetOptionsWhenProductTypeIsConfigurable()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->typeInstanceMock)
        );
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getSelectedAttributesInfo'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue(['attributes'])
        );
        $this->assertEquals(
            ['attributes', 'options'],
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetOptionsWhenProductTypeIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('simple'));
        $this->productMock->expects($this->never())->method('getTypeInstance');
        $this->assertEquals(
            ['options'],
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
