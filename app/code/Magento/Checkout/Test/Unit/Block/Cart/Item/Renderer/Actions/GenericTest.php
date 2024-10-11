<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    /**
     * @var Generic
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->model = $objectManagerHelper->getObject(
            Generic::class,
            []
        );
    }

    public function testGetItem()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertEquals($itemMock, $this->model->getItem());
    }

    public function testIsProductVisibleInSiteVisibility()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var Product|MockObject $productMock
         */
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertTrue($this->model->isProductVisibleInSiteVisibility());
    }

    public function testIsVirtual()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIsVirtual'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(true);

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertTrue($this->model->isVirtual());
    }
}
