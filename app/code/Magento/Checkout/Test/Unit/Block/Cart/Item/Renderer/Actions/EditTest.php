<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit;
use Magento\Quote\Model\Quote\Item;

class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Edit
     */
    protected $model;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $urlBuilderMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit::class,
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
         * @var Item|\PHPUnit\Framework\MockObject\MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var Product|\PHPUnit\Framework\MockObject\MockObject $itemMock
         */
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->model->setItem($itemMock);
        $this->assertEquals($configureUrl, $this->model->getConfigureUrl());
    }
}
