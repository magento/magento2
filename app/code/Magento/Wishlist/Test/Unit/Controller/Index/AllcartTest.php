<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AllcartTest extends \PHPUnit\Framework\TestCase
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
        $this->context = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->wishlistProvider = $this->createMock(\Magento\Wishlist\Controller\WishlistProvider::class);
        $this->itemCarrier = $this->createMock(\Magento\Wishlist\Model\ItemCarrier::class);
        $this->formKeyValidator = $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
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
    }

    protected function prepareContext()
    {
        $om = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
        $url = $this->createMock(\Magento\Framework\Url::class);
        $actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $redirect = $this->createMock(\Magento\Store\App\Response\Redirect::class);
        $view = $this->createMock(\Magento\Framework\App\View::class);
        $messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);

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
        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);
        
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
