<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    protected $model;

    /** @var UrlInterface|MockObject */
    protected $urlBuilderMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManagerHelper->getObject(
            Edit::class,
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
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var Product|MockObject $itemMock
         */
        $productMock = $this->getMockBuilder(Product::class)
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
