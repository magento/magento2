<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Block\Cart\Item\Renderer\Actions;

use Magento\Wishlist\Block\Cart\Item\Renderer\Actions\MoveToWishlist;
use Magento\Quote\Model\Quote\Item;
use Magento\Wishlist\Helper\Data;

class MoveToWishlistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MoveToWishlist
     */
    protected $model;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistHelperMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->wishlistHelperMock = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            'Magento\Wishlist\Block\Cart\Item\Renderer\Actions\MoveToWishlist',
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
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
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
