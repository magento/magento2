<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Checkout\Block\Cart\Item\Renderer\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

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
            'Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic',
            []
        );
    }

    public function testGetItemContext()
    {
        /**
         * @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder('Magento\Checkout\Block\Cart\Item\Renderer\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setItemContext($contextMock);
        $this->assertEquals($contextMock, $this->model->getItemContext());
    }

    public function testIsProductVisibleInSiteVisibility()
    {
        /**
         * @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder('Magento\Checkout\Block\Cart\Item\Renderer\Context')
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var AbstractItem|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item\AbstractItem')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMockForAbstractClass();

        /**
         * @var Product|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getQuoteItem')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('isVisibleInSiteVisibility')
            ->willReturn(true);

        $this->model->setItemContext($contextMock);
        $this->assertTrue($this->model->isProductVisibleInSiteVisibility());
    }
}
