<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use PHPUnit\Framework\TestCase;

class WishlistProviderTest extends TestCase
{
    /**
     * @var WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(RequestInterface::class);

        $this->wishlistFactory = $this->createPartialMock(WishlistFactory::class, ['create']);

        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomerId']);

        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->wishlistProvider = $objectManager->getObject(
            WishlistProvider::class,
            [
                'request' => $this->request,
                'wishlistFactory' => $this->wishlistFactory,
                'customerSession' => $this->customerSession,
                'messageManager' => $this->messageManager
            ]
        );
    }

    public function testGetWishlist()
    {
        $wishlist = $this->createMock(Wishlist::class);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithCustomer()
    {
        $wishlist = $this->createPartialMock(
            Wishlist::class,
            ['loadByCustomerId', 'getId', 'getCustomerId', '__wakeup']
        );
        $wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->willReturnSelf();
        $wishlist->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdAndCustomer()
    {
        $wishlist = $this->createPartialMock(
            Wishlist::class,
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup']
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $wishlist->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->request->expects($this->once())
            ->method('getParam')
            ->willReturn(1);

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdWithoutCustomer()
    {
        $wishlist = $this->createPartialMock(
            Wishlist::class,
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup']
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $wishlist->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->request->expects($this->once())
            ->method('getParam')
            ->willReturn(1);

        $this->assertFalse($this->wishlistProvider->getWishlist());
    }
}
