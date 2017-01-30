<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

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
        $this->context = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->wishlistProvider = $this->getMock('Magento\Wishlist\Controller\WishlistProvider', [], [], '', false);
        $this->itemCarrier = $this->getMock('Magento\Wishlist\Model\ItemCarrier', [], [], '', false);
        $this->formKeyValidator = $this->getMock('Magento\Framework\Data\Form\FormKey\Validator', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Forward')
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
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
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
        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $controller = $this->getController();
        $this->assertSame($this->resultForwardMock, $controller->execute());
    }

    public function testExecuteWithoutWishlist()
    {
        $this->formKeyValidator
            ->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->will($this->returnValue(true));
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue(null));
        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertSame($this->resultForwardMock, $this->getController()->execute());
    }

    public function testExecutePassed()
    {
        $url = 'http://redirect-url.com';
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', [], [], '', false);
        
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->will($this->returnValue(true));
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('qty')
            ->will($this->returnValue(2));
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));
        $this->itemCarrier->expects($this->once())
            ->method('moveAllToCart')
            ->with($wishlist, 2)
            ->willReturn($url);
        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($url)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }
}
