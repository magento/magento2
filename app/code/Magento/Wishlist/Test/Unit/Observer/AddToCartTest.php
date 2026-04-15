<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Observer\AddToCart as Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Customer\Model\Session as CustomerSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCartTest extends TestCase
{
    use MockCreationTrait;

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
     * @var CustomerSession|MockObject
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
        $this->checkoutSession = $this->createPartialMockWithReflection(
            Session::class,
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
        );
        $this->customerSession = $this->createPartialMockWithReflection(
            CustomerSession::class,
            ['setWishlistItemCount', 'isLoggedIn', 'getCustomerId']
        );
        $this->wishlistFactory = $this->createPartialMock(WishlistFactory::class, ['create']);
        $this->wishlist = $this->createMock(Wishlist::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);

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

        $eventObserver = $this->createMock(EventObserver::class);
        $event = $this->createPartialMockWithReflection(
            Event::class,
            ['getRequest', 'getResponse']
        );
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createPartialMock(
            ResponseHttp::class,
            ['setRedirect']
        );
        $wishlists = $this->createMock(Collection::class);
        $loadedWishlist = $this->createPartialMock(Wishlist::class, ['getId', 'delete']);

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

        /** @var $eventObserver EventObserver */
        $this->observer->execute($eventObserver);
    }
}
