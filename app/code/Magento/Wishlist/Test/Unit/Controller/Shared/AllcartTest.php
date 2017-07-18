<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Shared;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Controller\ResultFactory;

class AllcartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\Shared\Allcart
     */
    protected $allcartController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Wishlist\Controller\Shared\WishlistProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProviderMock;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemCarrierMock;

    /**
     * @var \Magento\Wishlist\Model\Wishlist|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Controller\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    protected function setUp()
    {
        $this->wishlistProviderMock = $this->getMockBuilder(\Magento\Wishlist\Controller\Shared\WishlistProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemCarrierMock = $this->getMockBuilder(\Magento\Wishlist\Model\ItemCarrier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistMock = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirectMock],
                    [ResultFactory::TYPE_FORWARD, [], $this->resultForwardMock]
                ]
            );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $this->objectManagerHelper->getObject(
            \Magento\Framework\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
        $this->allcartController = $this->objectManagerHelper->getObject(
            \Magento\Wishlist\Controller\Shared\Allcart::class,
            [
                'context' => $this->context,
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
