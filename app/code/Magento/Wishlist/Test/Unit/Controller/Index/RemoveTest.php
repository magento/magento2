<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Store\App\Response\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $url;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    protected function setUp()
    {
        $this->context = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
        $this->request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->wishlistProvider = $this->getMock(
            \Magento\Wishlist\Controller\WishlistProvider::class,
            [],
            [],
            '',
            false
        );
        $this->redirect = $this->getMock(\Magento\Store\App\Response\Redirect::class, [], [], '', false);
        $this->om = $this->getMock(\Magento\Framework\App\ObjectManager::class, [], [], '', false);
        $this->messageManager = $this->getMock(\Magento\Framework\Message\Manager::class, [], [], '', false);
        $this->url = $this->getMock(\Magento\Framework\Url::class, [], [], '', false);
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        unset(
            $this->context,
            $this->request,
            $this->wishlistProvider,
            $this->redirect,
            $this->om,
            $this->messageManager,
            $this->url
        );
    }

    protected function prepareContext()
    {
        $eventManager = $this->getMock(\Magento\Framework\Event\Manager::class, null, [], '', false);
        $actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->om);
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context
            ->expects($this->any())
            ->method('getEventManager')
            ->willReturn($eventManager);
        $this->context
            ->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->url);
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
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
    }

    public function getController()
    {
        $this->prepareContext();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        return new \Magento\Wishlist\Controller\Index\Remove(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator
        );
    }

    public function testExecuteWithInvalidFormKey()
    {
        $this->prepareContext();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $controller = new \Magento\Wishlist\Controller\Index\Remove(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator
        );

        $this->assertSame($this->resultRedirectMock, $controller->execute());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteWithoutItem()
    {
        $item = $this->getMock(\Magento\Wishlist\Model\Item::class, [], [], '', false);
        $item
            ->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $item
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();

        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn(1);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(\Magento\Wishlist\Model\Item::class)
            ->willReturn($item);

        $this->getController()->execute();
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteWithoutWishlist()
    {
        $item = $this->getMock(\Magento\Wishlist\Model\Item::class, [], [], '', false);
        $item
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(2);

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('item')
            ->willReturn(1);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(\Magento\Wishlist\Model\Item::class)
            ->willReturn($item);
        
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(2)
            ->willReturn(null);

        $this->getController()->execute();
    }

    public function testExecuteCanNotSaveWishlist()
    {
        $referer = 'http://referer-url.com';

        $exception = new \Magento\Framework\Exception\LocalizedException(__('Message'));
        $wishlist = $this->getMock(\Magento\Wishlist\Model\Wishlist::class, [], [], '', false);
        $wishlist
            ->expects($this->once())
            ->method('save')
            ->willThrowException($exception);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(2)
            ->willReturn($wishlist);

        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t delete the item from Wish List right now because of an error: Message.')
            ->willReturn(true);

        $wishlistHelper = $this->getMock(\Magento\Wishlist\Helper\Data::class, [], [], '', false);
        $wishlistHelper
            ->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->om
            ->expects($this->once())
            ->method('get')
            ->with(\Magento\Wishlist\Helper\Data::class)
            ->will($this->returnValue($wishlistHelper));

        $item = $this->getMock(\Magento\Wishlist\Model\Item::class, [], [], '', false);
        $item
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(2);
        $item
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(\Magento\Wishlist\Model\Item::class)
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->willReturn($referer);
        $this->request
            ->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['item', null, 1],
                    ['referer_url', null, $referer],
                    ['uenc', null, $referer]
                ]
            );

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($referer)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }

    public function testExecuteCanNotSaveWishlistAndWithRedirect()
    {
        $referer = 'http://referer-url.com';

        $exception = new \Exception('Message');
        $wishlist = $this->getMock(\Magento\Wishlist\Model\Wishlist::class, [], [], '', false);
        $wishlist
            ->expects($this->once())
            ->method('save')
            ->willThrowException($exception);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(2)
            ->willReturn($wishlist);

        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t delete the item from the Wish List right now.')
            ->willReturn(true);

        $wishlistHelper = $this->getMock(\Magento\Wishlist\Helper\Data::class, [], [], '', false);
        $wishlistHelper
            ->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->om
            ->expects($this->once())
            ->method('get')
            ->with(\Magento\Wishlist\Helper\Data::class)
            ->will($this->returnValue($wishlistHelper));

        $item = $this->getMock(\Magento\Wishlist\Model\Item::class, [], [], '', false);
        $item
            ->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(2);
        $item
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(\Magento\Wishlist\Model\Item::class)
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getServer')
            ->with('HTTP_REFERER')
            ->willReturn($referer);
        $this->request
            ->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['item', null, 1],
                    ['referer_url', null, $referer],
                    ['uenc', null, false]
                ]
            );

        $this->url
            ->expects($this->once())
            ->method('getUrl')
            ->with('*/*')
            ->willReturn('http://test.com/frontname/module/controller/action');

        $this->redirect
            ->expects($this->once())
            ->method('getRedirectUrl')
            ->willReturn('http://test.com/frontname/module/controller/action');

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with('http://test.com/frontname/module/controller/action')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }
}
