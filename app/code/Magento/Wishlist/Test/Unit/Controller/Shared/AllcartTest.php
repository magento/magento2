<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Controller\Shared;

class AllcartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\Shared\WishlistProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemCarrier;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->wishlistProvider = $this->getMock(
            '\Magento\Wishlist\Controller\Shared\WishlistProvider',
            [],
            [],
            '',
            false
        );
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->itemCarrier = $this->getMock('Magento\Wishlist\Model\ItemCarrier', [], [], '', false);
        $this->context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
    }

    protected function prepareContext()
    {
        $om = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', null, [], '', false);
        $url = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $redirect = $this->getMock('\Magento\Store\App\Response\Redirect', [], [], '', false);
        $view = $this->getMock('Magento\Framework\App\View', [], [], '', false);
        $messageManager = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($om));
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context
            ->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $this->context
            ->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($url));
        $this->context
            ->expects($this->any())
            ->method('getActionFlag')
            ->will($this->returnValue($actionFlag));
        $this->context
            ->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($redirect));
        $this->context
            ->expects($this->any())
            ->method('getView')
            ->will($this->returnValue($view));
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
    }

    public function getController()
    {
        $this->prepareContext();
        return new \Magento\Wishlist\Controller\Shared\Allcart(
            $this->context,
            $this->wishlistProvider,
            $this->itemCarrier
        );
    }

    public function testExecuteWithNoWishlist()
    {
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn(false);

        $this->request
            ->expects($this->once())
            ->method('initForward')
            ->will($this->returnValue(true));
        $this->request
            ->expects($this->once())
            ->method('setActionName')
            ->with('noroute')
            ->will($this->returnValue(true));
        $this->request
            ->expects($this->once())
            ->method('setDispatched')
            ->with(false)
            ->will($this->returnValue(true));

        $controller = $this->getController();
        $controller->execute();
    }

    public function testExecuteWithWishlist()
    {
        $wishlist = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
            ->disableOriginalConstructor()
            ->getMock();
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('qty')
            ->will($this->returnValue(2));

        $this->itemCarrier
            ->expects($this->once())
            ->method('moveAllToCart')
            ->with($wishlist, 2)
            ->will($this->returnValue('http://redirect-url.com'));

        $this->response
            ->expects($this->once())
            ->method('setRedirect')
            ->will($this->returnValue('http://redirect-url.com'));

        $controller = $this->getController();
        $controller->execute();
    }
}
