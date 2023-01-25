<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\PriceModifier;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifier\Composite;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /**
     * @var Composite
     */
    protected $compositeModel;

    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $priceModifierMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->productMock = $this->createMock(Product::class);
        $this->priceModifierMock = $this->getMockForAbstractClass(PriceModifierInterface::class);
    }

    public function testModifyPriceIfModifierExists()
    {
        $this->compositeModel = new Composite(
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
        $this->compositeModel = new Composite(
            $this->objectManagerMock,
            []
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->assertEquals(100, $this->compositeModel->modifyPrice(100, $this->productMock));
    }
}
