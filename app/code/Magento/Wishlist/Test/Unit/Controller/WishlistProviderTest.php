<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller;

class WishlistProviderTest extends \PHPUnit_Framework_TestCase
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
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMock(\Magento\Framework\App\RequestInterface::class);

        $this->wishlistFactory = $this->getMock(
            \Magento\Wishlist\Model\WishlistFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->customerSession = $this->getMock(
            \Magento\Customer\Model\Session::class,
            ['getCustomerId'],
            [],
            '',
            false
        );

        $this->messageManager = $this->getMock(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            [],
            '',
            false
        );

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
        $wishlist = $this->getMock(\Magento\Wishlist\Model\Wishlist::class, [], [], '', false);

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithCustomer()
    {
        $wishlist = $this->getMock(
            \Magento\Wishlist\Model\Wishlist::class,
            ['loadByCustomerId', 'getId', 'getCustomerId', '__wakeup'],
            [],
            '',
            false
        );
        $wishlist->expects($this->once())
            ->method('loadByCustomerId')
            ->will($this->returnSelf());
        $wishlist->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdAndCustomer()
    {
        $wishlist = $this->getMock(
            \Magento\Wishlist\Model\Wishlist::class,
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup'],
            [],
            '',
            false
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $wishlist->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(1));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->assertEquals($wishlist, $this->wishlistProvider->getWishlist());
    }

    public function testGetWishlistWithIdWithoutCustomer()
    {
        $wishlist = $this->getMock(
            \Magento\Wishlist\Model\Wishlist::class,
            ['loadByCustomerId', 'load', 'getId', 'getCustomerId', '__wakeup'],
            [],
            '',
            false
        );

        $wishlist->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());
        $wishlist->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $wishlist->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue(1));

        $this->wishlistFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($wishlist));

        $this->request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue(1));

        $this->assertEquals(false, $this->wishlistProvider->getWishlist());
    }
}
