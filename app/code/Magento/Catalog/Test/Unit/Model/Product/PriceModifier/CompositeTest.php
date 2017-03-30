<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\PriceModifier;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\PriceModifier\Composite
     */
    protected $compositeModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModifierMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $this->priceModifierMock = $this->getMock(\Magento\Catalog\Model\Product\PriceModifierInterface::class);
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
        )->will(
            $this->returnValue($this->priceModifierMock)
        );
        $this->priceModifierMock->expects(
            $this->once()
        )->method(
            'modifyPrice'
        )->with(
            100,
            $this->productMock
        )->will(
            $this->returnValue(150)
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
