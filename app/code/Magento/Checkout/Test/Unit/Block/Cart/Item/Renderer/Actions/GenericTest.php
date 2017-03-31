<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Quote\Model\Quote\Item;

class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generic
     */
    protected $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic::class,
            []
        );
    }

    public function testGetItem()
    {
        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertEquals($itemMock, $this->model->getItem());
    }

    public function testIsProductVisibleInSiteVisibility()
    {
        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var Product|\PHPUnit_Framework_MockObject_MockObject $productMock
         */
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
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
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsVirtual'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(true);

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertTrue($this->model->isVirtual());
    }
}
