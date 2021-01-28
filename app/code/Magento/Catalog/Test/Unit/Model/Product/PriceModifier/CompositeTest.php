<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\PriceModifier;

class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier\Composite
     */
    protected $compositeModel;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceModifierMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->priceModifierMock = $this->createMock(\Magento\Catalog\Model\Product\PriceModifierInterface::class);
    }

    public function testModifyPriceIfModifierExists()
    {
        $this->compositeModel = new \Magento\Catalog\Model\Product\PriceModifier\Composite(
            $this->objectManagerMock,
            ['some_class_name']
        );
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'some_class_name'
        )->willReturn(
            $this->priceModifierMock
        );
        $this->priceModifierMock->expects(
            $this->once()
        )->method(
            'modifyPrice'
        )->with(
            100,
            $this->productMock
        )->willReturn(
            150
        );
        $this->assertEquals(150, $this->compositeModel->modifyPrice(100, $this->productMock));
    }

    public function testModifyPriceIfModifierNotExists()
    {
        $this->compositeModel = new \Magento\Catalog\Model\Product\PriceModifier\Composite(
            $this->objectManagerMock,
            []
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->assertEquals(100, $this->compositeModel->modifyPrice(100, $this->productMock));
    }
}
