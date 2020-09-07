<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
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

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->wishlistHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            MoveToWishlist::class,
            [
                'wishlistHelper' => $this->wishlistHelperMock,
            ]
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
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

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
