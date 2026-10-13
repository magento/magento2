<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item;
use Magento\Wishlist\Block\Cart\Item\Renderer\Actions\MoveToWishlist;
use Magento\Wishlist\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MoveToWishlistTest extends TestCase
{
    /**
     * @var MoveToWishlist
     */
    protected $model;

    /** @var Data|MockObject */
    protected $wishlistHelperMock;

    /** @var Context|MockObject */
    protected $contextMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->wishlistHelperMock = $this->createMock(Data::class);

        $this->model = new MoveToWishlist(
            $this->contextMock,
            $this->wishlistHelperMock
        );
    }

    public function testIsAllowInCart()
    {
        $this->wishlistHelperMock->expects($this->once())
            ->method('isAllowInCart')
            ->willReturn(true);

        $this->assertTrue($this->model->isAllowInCart());
    }

    public function testGetMoveFromCartParams()
    {
        $itemId = 45;
        $json = '{json;}';

        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->createMock(Item::class);

        $itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);

        $this->wishlistHelperMock->expects($this->once())
            ->method('getMoveFromCartParams')
            ->with($itemId)
            ->willReturn($json);

        $this->model->setItem($itemMock);
        $this->assertEquals($json, $this->model->getMoveFromCartParams());
    }
}
