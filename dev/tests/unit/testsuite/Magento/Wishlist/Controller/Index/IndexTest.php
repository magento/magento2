<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var \Magento\Store\App\Response\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->wishlistProvider = $this->getMock('Magento\Wishlist\Controller\WishlistProvider', [], [], '', false);
        $this->view = $this->getMock('Magento\Framework\App\View', [], [], '', false);
        $this->redirect = $this->getMock('\Magento\Store\App\Response\Redirect', [], [], '', false);
    }

    protected function prepareContext()
    {
        $om = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', null, [], '', false);
        $url = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $messageManager = $this->getMock('Magento\Framework\Message\Manager', [], [], '', false);

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($om);
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context
            ->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $this->context
            ->expects($this->any())
            ->method('getUrl')
            ->willReturn($url);
        $this->context
            ->expects($this->any())
            ->method('getActionFlag')
            ->willReturn($actionFlag);
        $this->context
            ->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $this->context
            ->expects($this->any())
            ->method('getView')
            ->willReturn($this->view);
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManager);
    }

    public function getController()
    {
        $this->prepareContext();
        return new \Magento\Wishlist\Controller\Index\Index(
            $this->context,
            $this->customerSession,
            $this->wishlistProvider
        );
    }

    public function testExecuteWithoutWishlist()
    {
        $this->setExpectedException('Magento\Framework\App\Action\NotFoundException');

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);

        $this->getController()->execute();
    }

    public function testExecutePassed()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', [], [], '', false);

        $block = $this->getMock('Magento\Ui\Component\Form\Element\Input', [], [], '', false);
        $block
            ->expects($this->at(0))
            ->method('__call')
            ->with('setRefererUrl', ['http://referer-url-test.com'])
            ->willReturn(true);
        $block
            ->expects($this->at(1))
            ->method('__call')
            ->with('setRefererUrl', ['http://referer-url.com'])
            ->willReturn(true);

        $this->redirect
            ->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn('http://referer-url-test.com');

        $layout = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $layout
            ->expects($this->once())
            ->method('getBlock')
            ->with('customer.wishlist')
            ->willReturn($block);
        $layout
            ->expects($this->once())
            ->method('initMessages')
            ->willReturn(true);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->view
            ->expects($this->once())
            ->method('loadLayout')
            ->willReturn(true);
        $this->view
            ->expects($this->exactly(2))
            ->method('getLayout')
            ->willReturn($layout);
        $this->view
            ->expects($this->once())
            ->method('renderLayout')
            ->willReturn(true);

        $this->customerSession
            ->expects($this->once())
            ->method('__call')
            ->with('getAddActionReferer', [true])
            ->willReturn('http://referer-url.com');

        $this->getController()->execute();
    }
}
