<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Observer\AddToCart as Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCartTest extends TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    /**
     * @var Session|MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSession;

    /**
     * @var WishlistFactory|MockObject
     */
    protected $wishlistFactory;

    /**
     * @var Wishlist|MockObject
     */
    protected $wishlist;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    protected function setUp(): void
    {
        $this->checkoutSession = $this->getMockBuilder(
            Session::class
        )->addMethods(
            [
                'getSharedWishlist',
                'getWishlistPendingMessages',
                'getWishlistPendingUrls',
                'getWishlistIds',
                'getSingleWishlistId',
                'setSingleWishlistId',
                'setWishlistIds',
                'setWishlistPendingUrls',
                'setWishlistPendingMessages',
                'setNoCartRedirect',
            ]
        )->disableOriginalConstructor()
            ->getMock();
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['setWishlistItemCount'])
            ->onlyMethods(['isLoggedIn', 'getCustomerId'])
            ->getMock();
        $this->wishlistFactory = $this->getMockBuilder(WishlistFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->wishlist = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();

        $this->wishlistFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->wishlist);

        $this->observer = new Observer(
            $this->checkoutSession,
            $this->customerSession,
            $this->wishlistFactory,
            $this->messageManager
        );
    }

    public function testExecute()
    {
        $wishlistId = 1;
        $customerId = 2;
        $url = 'http://some.pending/url';
        $message = 'some error msg';

        $eventObserver = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->addMethods(['getRequest', 'getResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->getMockForAbstractClass();
        $wishlists = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $loadedWishlist = $this->getMockBuilder(Wishlist::class)
            ->onlyMethods(['getId', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventObserver->expects($this->any())->method('getEvent')->willReturn($event);

        $request->expects($this->any())->method('getParam')->with('wishlist_next')->willReturn(true);
        $event->expects($this->once())->method('getRequest')->willReturn($request);

        $this->checkoutSession->expects($this->once())->method('getSharedWishlist');
        $this->checkoutSession->expects($this->once())->method('getWishlistPendingMessages')->willReturn([$message]);
        $this->checkoutSession->expects($this->once())->method('getWishlistPendingUrls')->willReturn([$url]);
        $this->checkoutSession->expects($this->once())->method('getWishlistIds');
        $this->checkoutSession->expects($this->once())->method('getSingleWishlistId')->willReturn($wishlistId);

        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->with($this->logicalOr($customerId, true))
            ->willReturnSelf();
        $this->wishlist->expects($this->once())
            ->method('getItemCollection')
            ->willReturn($wishlists);
        $loadedWishlist->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);
        $loadedWishlist->expects($this->once())
            ->method('delete');
        $wishlists->expects($this->once())
            ->method('load')
            ->willReturn([$loadedWishlist]);
        $this->checkoutSession->expects($this->once())
            ->method('setWishlistIds')
            ->with([])
            ->willReturnSelf();
        $this->checkoutSession->expects($this->once())
            ->method('setSingleWishlistId')
            ->with(null)
            ->willReturnSelf();
        $this->checkoutSession->expects($this->once())
            ->method('setWishlistPendingUrls')
            ->with([])
            ->willReturnSelf();
        $this->checkoutSession->expects($this->once())
            ->method('setWishlistPendingMessages')
            ->with([])
            ->willReturnSelf();
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($message)
            ->willReturnSelf();
        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);
        $response->expects($this->once())
            ->method('setRedirect')
            ->with($url);
        $this->checkoutSession->expects($this->once())
            ->method('setNoCartRedirect')
            ->with(true);

        /** @var $eventObserver \Magento\Framework\Event\Observer */
        $this->observer->execute($eventObserver);
    }
}
