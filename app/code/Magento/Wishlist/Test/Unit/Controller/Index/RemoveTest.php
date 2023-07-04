<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\Url;
use Magento\Store\App\Response\Redirect;
use Magento\Wishlist\Controller\Index\Remove;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Event\Manager as EventManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveTest extends TestCase
{
    /**
     * @var WishlistProvider|MockObject
     */
    protected $wishlistProvider;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var Redirect|MockObject
     */
    protected $redirect;

    /**
     * @var ObjectManager|MockObject
     */
    protected $om;

    /**
     * @var Manager|MockObject
     */
    protected $messageManager;

    /**
     * @var Url|MockObject
     */
    protected $url;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var ResultRedirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var Validator|MockObject
     */
    protected $formKeyValidator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(Http::class);
        $this->wishlistProvider = $this->createMock(WishlistProvider::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->om = $this->createMock(ObjectManager::class);
        $this->messageManager = $this->createMock(Manager::class);
        $this->url = $this->createMock(Url::class);
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(ResultRedirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);

        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
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

    /**
     * @return void
     */
    protected function prepareContext(): void
    {
        $eventManager = $this->createMock(EventManager::class);
        $actionFlag = $this->createMock(ActionFlag::class);

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

    /**
     * @return Remove
     */
    public function getController(): Remove
    {
        $this->prepareContext();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        return new Remove(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithInvalidFormKey(): void
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

        $controller = new Remove(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator
        );

        $this->assertSame($this->resultRedirectMock, $controller->execute());
    }

    /**
     * @return void
     */
    public function testExecuteWithoutItem(): void
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $item = $this->createMock(Item::class);
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
            ->with(Item::class)
            ->willReturn($item);

        $this->getController()->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithoutWishlist(): void
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
        $item = $this->createMock(Item::class);
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
            ->method('getParam')
            ->with('item')
            ->willReturn(1);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($item);

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(2)
            ->willReturn(null);

        $this->getController()->execute();
    }

    /**
     * @return void
     */
    public function testExecuteCanNotSaveWishlist(): void
    {
        $referer = 'http://referer-url.com';

        $exception = new LocalizedException(__('Message'));
        $wishlist = $this->createMock(Wishlist::class);
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
            ->method('addErrorMessage')
            ->with('We can\'t delete the item from Wish List right now because of an error: Message.')
            ->willReturn(true);

        $wishlistHelper = $this->createMock(Data::class);
        $wishlistHelper
            ->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->om
            ->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($wishlistHelper);

        $item = $this->createMock(Item::class);
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
            ->with(Item::class)
            ->willReturn($item);

        $this->redirect
            ->method('getRefererUrl')
            ->with()
            ->willReturn($referer);
        $this->request
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

    /**
     * @return void
     */
    public function testExecuteCanNotSaveWishlistAndWithRedirect(): void
    {
        $referer = 'http://referer-url.com';

        $exception = new \Exception('Message');
        $wishlist = $this->createMock(Wishlist::class);
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
            ->method('addErrorMessage')
            ->with('We can\'t delete the item from the Wish List right now.')
            ->willReturn(true);

        $wishlistHelper = $this->createMock(Data::class);
        $wishlistHelper
            ->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->om
            ->expects($this->once())
            ->method('get')
            ->with(Data::class)
            ->willReturn($wishlistHelper);

        $item = $this->createMock(Item::class);
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
            ->with(Item::class)
            ->willReturn($item);

        $this->request
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
