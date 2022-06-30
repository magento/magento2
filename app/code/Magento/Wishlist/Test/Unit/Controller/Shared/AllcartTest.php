<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Shared;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Wishlist\Controller\Shared\Allcart;
use Magento\Wishlist\Controller\Shared\WishlistProvider;
use Magento\Wishlist\Model\ItemCarrier;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Wishlist\Controller\Shared\Allcart.
 */
class AllcartTest extends TestCase
{
    /**
     * @var Allcart
     */
    private $allcartController;

    /**
     * @var WishlistProvider|MockObject
     */
    private $wishlistProviderMock;

    /**
     * @var ItemCarrier|MockObject
     */
    private $itemCarrierMock;

    /**
     * @var Wishlist|MockObject
     */
    private $wishlistMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Forward|MockObject
     */
    private $resultForwardMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->wishlistProviderMock = $this->createMock(WishlistProvider::class);
        $this->itemCarrierMock = $this->createMock(ItemCarrier::class);
        $this->wishlistMock = $this->createMock(Wishlist::class);
        $this->requestMock = $this->createMock(Http::class);
        $resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->resultForwardMock = $this->createMock(Forward::class);

        $resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirectMock],
                    [ResultFactory::TYPE_FORWARD, [], $this->resultForwardMock]
                ]
            );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $context = $objectManagerHelper->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultFactory' => $resultFactoryMock
            ]
        );
        $this->allcartController = $objectManagerHelper->getObject(
            Allcart::class,
            [
                'context' => $context,
                'wishlistProvider' => $this->wishlistProviderMock,
                'itemCarrier' => $this->itemCarrierMock
            ]
        );
    }

    public function testExecuteWithWishlist()
    {
        $url = 'http://redirect-url.com';
        $quantity = 2;

        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn($this->wishlistMock);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('qty')
            ->willReturn($quantity);
        $this->itemCarrierMock->expects($this->once())
            ->method('moveAllToCart')
            ->with($this->wishlistMock, 2)
            ->willReturn($url);
        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->allcartController->execute());
    }

    public function testExecuteWithNoWishlist()
    {
        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->willReturn(false);
        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertSame($this->resultForwardMock, $this->allcartController->execute());
    }
}
