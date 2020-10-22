<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Order\Admin\Item\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Order\Admin\Item\Plugin\Configurable;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @var MockObject
     */
    protected $productFactoryMock;

    /**
     * @var MockObject
     */
    protected $itemMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->itemMock = $this->createPartialMock(
            Item::class,
            ['getProductType', 'getProductOptions']
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->productMock = $this->createMock(Product::class);
        $this->subjectMock = $this->createMock(\Magento\Sales\Model\Order\Admin\Item::class);
        $this->configurable = new Configurable(
            $this->productFactoryMock
        );
    }

    public function testAroundGetNameIfProductIsConfigurable()
    {
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductType'
        )->willReturn(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->willReturn(
            ['simple_name' => 'simpleName']
        );
        $this->assertEquals(
            'simpleName',
            $this->configurable->aroundGetName($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetNameIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->willReturn('simple');
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
        )->willReturn(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->willReturn(
            ['simple_sku' => 'simpleName']
        );
        $this->assertEquals(
            'simpleName',
            $this->configurable->aroundGetSku($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetSkuIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->willReturn('simple');
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
        )->willReturn(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        $this->itemMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->willReturn(
            ['simple_sku' => 'simpleName']
        );
        $this->productFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->productMock
        );
        $this->productMock->expects(
            $this->once()
        )->method(
            'getIdBySku'
        )->with(
            'simpleName'
        )->willReturn(
            'id'
        );
        $this->assertEquals(
            'id',
            $this->configurable->aroundGetProductId($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }

    public function testAroundGetProductIdIfProductIsSimple()
    {
        $this->itemMock->expects($this->once())->method('getProductType')->willReturn('simple');
        $this->itemMock->expects($this->never())->method('getProductOptions');
        $this->assertEquals(
            'Expected',
            $this->configurable->aroundGetProductId($this->subjectMock, $this->closureMock, $this->itemMock)
        );
    }
}
