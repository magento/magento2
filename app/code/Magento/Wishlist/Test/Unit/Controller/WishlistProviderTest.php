<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller;

class WishlistProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Set up mock objects for tested class
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $this->wishlistFactory = $this->createPartialMock(\Magento\Wishlist\Model\WishlistFactory::class, ['create']);

        $this->customerSession = $this->createPartialMock(\Magento\Customer\Model\Session::class, ['getCustomerId']);

        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->wishlistProvider = $objectManager->getObject(
            \Magento\Wishlist\Controller\WishlistProvider::class,
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
        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->willReturn($wishlist);

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithCustomer()
    {
        $wishlist = $this->createPartialMock(
            \Magento\Wishlist\Model\Wishlist::class,
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
            \Magento\Wishlist\Model\Wishlist::class,
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
            \Magento\Wishlist\Model\Wishlist::class,
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
