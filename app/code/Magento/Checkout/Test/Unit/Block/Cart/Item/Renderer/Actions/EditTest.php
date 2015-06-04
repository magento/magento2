<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit;
use Magento\Checkout\Block\Cart\Item\Renderer\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Edit
     */
    protected $model;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit',
            [
                'urlBuilder' => $this->urlBuilderMock,
            ]
        );
    }

    public function testGetConfigureUrl()
    {
        $itemId = 45;
        $productId = 12;
        $configureUrl = 'configure url';

        /**
         * @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder('\Magento\Checkout\Block\Cart\Item\Renderer\Context')
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var AbstractItem|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item\AbstractItem')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getId'])
            ->getMockForAbstractClass();

        /**
         * @var Product|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->exactly(2))
            ->method('getQuoteItem')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('checkout/cart/configure', ['id' => $itemId, 'product_id' => $productId])
            ->willReturn($configureUrl);

        $this->model->setItemContext($contextMock);
        $this->assertEquals($configureUrl, $this->model->getConfigureUrl());
    }
}
