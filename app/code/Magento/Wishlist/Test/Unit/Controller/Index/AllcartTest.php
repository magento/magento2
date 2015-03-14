<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Controller\Index;

class AllcartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemCarrier;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

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

    protected function setUp()
    {
        $this->context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->wishlistProvider = $this->getMock('Magento\Wishlist\Controller\WishlistProvider', [], [], '', false);
        $this->itemCarrier = $this->getMock('Magento\Wishlist\Model\ItemCarrier', [], [], '', false);
        $this->formKeyValidator = $this->getMock('Magento\Framework\Data\Form\FormKey\Validator', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
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
        return new \Magento\Wishlist\Controller\Index\Allcart(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator,
            $this->itemCarrier
        );
    }

    public function testExecuteInvalidFormKey()
    {
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->will($this->returnValue(false));

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

    public function testExecuteWithoutWishlist()
    {
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->will($this->returnValue(true));

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

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue(null));


        $this->getController()->execute();
    }

    public function testExecutePassed()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', [], [], '', false);
        
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->will($this->returnValue(true));

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('qty')
            ->will($this->returnValue(2));

        $this->response
            ->expects($this->once())
            ->method('setRedirect')
            ->will($this->returnValue('http://redirect-url.com'));

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));
        
        $this->itemCarrier
            ->expects($this->once())
            ->method('moveAllToCart')
            ->with($wishlist, 2)
            ->will($this->returnValue('http://redirect-url.com'));

        $this->getController()->execute();
    }
}
