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
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Wishlist\Model\ItemCarrier|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemCarrier;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Controller\Result\Forward|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardMock;

    protected function setUp(): void
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
            ->willReturn($redirect);
        $this->context
            ->expects($this->any())
            ->method('getView')
            ->willReturn($view);
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
    }

    /**
     * @return \Magento\Wishlist\Controller\Index\Allcart
     */
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
            ->willReturn(false);
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
            ->willReturn(true);
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);
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
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('qty')
            ->willReturn(2);
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);
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
