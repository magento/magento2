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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeInstanceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp(): void
    {
        $this->itemMock = $this->createMock(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->typeInstanceMock = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
            ['getSelectedAttributesInfo', '__wakeup']
        );
        $this->itemMock->expects($this->once())->method('getProduct')->willReturn($this->productMock);
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
        )->willReturn(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->typeInstanceMock
        );
        $this->typeInstanceMock->expects(
            $this->once()
        )->method(
            'getSelectedAttributesInfo'
        )->with(
            $this->productMock
        )->willReturn(
            ['attributes']
        );
        $this->assertEquals(
            ['attributes', 'options'],
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetOptionsWhenProductTypeIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productMock->expects($this->never())->method('getTypeInstance');
        $this->assertEquals(
            ['options'],
            $this->plugin->aroundGetOptions($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
