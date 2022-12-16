<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Url;
use Magento\Wishlist\Controller\Index\UpdateItemOptions;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateItemOptionsTest extends TestCase
{
    /**
     * @var ProductRepository|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var WishlistProvider|MockObject
     */
    private $wishlistProviderMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManagerMock;

    /**
     * @var MessageManager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Url|MockObject
     */
    private $urlMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var EventManager|MockObject
     */
    private $eventManagerMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirectMock;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->contextMock = $this->createMock(Context::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->wishlistProviderMock = $this->createMock(WishlistProvider::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->messageManagerMock = $this->createMock(MessageManager::class);
        $this->urlMock = $this->createMock(Url::class);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->eventManagerMock = $this->createMock(EventManager::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->resultRedirectMock = $this->createMock(Redirect::class);
        $this->formKeyValidator = $this->createMock(Validator::class);

        $this->resultFactoryMock
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset(
            $this->productRepositoryMock,
            $this->contextMock,
            $this->requestMock,
            $this->wishlistProviderMock,
            $this->objectManagerMock,
            $this->messageManagerMock,
            $this->urlMock,
            $this->eventManagerMock
        );
    }

    /**
     * Prepare context
     *
     * @return void
     */
    public function prepareContext(): void
    {
        $actionFlag = $this->createMock(ActionFlag::class);

        $this->contextMock
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);
        $this->contextMock
            ->method('getUrl')
            ->willReturn($this->urlMock);
        $this->contextMock
            ->method('getActionFlag')
            ->willReturn($actionFlag);
        $this->contextMock
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
    }

    /**
     * Get controller.
     *
     * @param bool $formKeyValid
     *
     * @return UpdateItemOptions
     */
    private function getController(bool $formKeyValid = true): UpdateItemOptions
    {
        $this->prepareContext();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn($formKeyValid);

        return new UpdateItemOptions(
            $this->contextMock,
            $this->customerSessionMock,
            $this->wishlistProviderMock,
            $this->productRepositoryMock,
            $this->formKeyValidator
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithInvalidFormKey(): void
    {
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController(false)->execute());
    }

    /**
     * Test execute without product id.
     *
     * @return void
     */
    public function testExecuteWithoutProductId(): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('product')
            ->willReturn(null);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/', [])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }

    /**
     * Test execute without product.
     *
     * @return void
     */
    public function testExecuteWithoutProduct(): void
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('product')
            ->willReturn(2);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willThrowException(new NoSuchEntityException());

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with('We can\'t specify a product.')
            ->willReturn(true);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/', [])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }

    /**
     * Test execute without wish list.
     *
     * @return void
     */
    public function testExecuteWithoutWishList(): void
    {
        $product = $this->createMock(Product::class);
        $item = $this->createMock(Item::class);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['product', null], ['id', null])
            ->willReturnOnConsecutiveCalls(2, 3);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $this->messageManagerMock
            ->expects($this->never())
            ->method('addErrorMessage')
            ->with('We can\'t specify a product.')
            ->willReturn(true);

        $item
            ->expects($this->once())
            ->method('load')
            ->with(3)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(12);

        $this->wishlistProviderMock
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn(null);

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($item);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/', [])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }

    /**
     * Test execute add success exception.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteAddSuccessException(): void
    {
        $wishlist = $this->createMock(Wishlist::class);
        $product = $this->createMock(Product::class);
        $item = $this->createMock(Item::class);
        $helper = $this->createMock(Data::class);

        $helper
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(true);

        $wishlist
            ->expects($this->once())
            ->method('getItem')
            ->with(3)
            ->willReturn($item);
        $wishlist
            ->expects($this->once())
            ->method('updateItem')
            ->with(3, new DataObject([]))
            ->willReturnSelf();
        $wishlist
            ->expects($this->once())
            ->method('save')
            ->willReturn(null);
        $wishlist
            ->expects($this->once())
            ->method('getId')
            ->willReturn(56);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);
        $product
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Test name');

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['product', null], ['id', null])
            ->willReturnOnConsecutiveCalls(2, 3);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $item
            ->expects($this->once())
            ->method('load')
            ->with(3)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(12);

        $this->wishlistProviderMock
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn($wishlist);

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($item);

        $this->requestMock
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->objectManagerMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($helper);

        $this->eventManagerMock
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Test name has been updated in your Wish List.', null)
            ->willThrowException(new LocalizedException(__('error-message')));
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with('error-message', null)
            ->willReturn(true);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*', ['wishlist_id' => 56])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }

    /**
     * Test execute add success critical exception.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteAddSuccessCriticalException(): void
    {
        $wishlist = $this->createMock(Wishlist::class);
        $product = $this->createMock(Product::class);
        $item = $this->createMock(Item::class);
        $helper = $this->createMock(Data::class);
        $logger = $this->createMock(Monolog::class);
        $exception = new \Exception();

        $logger
            ->expects($this->once())
            ->method('critical')
            ->with($exception);

        $helper
            ->expects($this->exactly(2))
            ->method('calculate')
            ->willReturn(true);

        $wishlist
            ->expects($this->once())
            ->method('getItem')
            ->with(3)
            ->willReturn($item);
        $wishlist
            ->expects($this->once())
            ->method('updateItem')
            ->with(3, new DataObject([]))
            ->willReturnSelf();
        $wishlist
            ->expects($this->once())
            ->method('save')
            ->willReturn(null);
        $wishlist
            ->expects($this->once())
            ->method('getId')
            ->willReturn(56);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);
        $product
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Test name');

        $this->requestMock
            ->method('getParam')
            ->withConsecutive(['product', null], ['id', null])
            ->willReturnOnConsecutiveCalls(2, 3);

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $item
            ->expects($this->once())
            ->method('load')
            ->with(3)
            ->willReturnSelf();
        $item
            ->expects($this->once())
            ->method('__call')
            ->with('getWishlistId')
            ->willReturn(12);

        $this->wishlistProviderMock
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn($wishlist);

        $this->objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($item);

        $this->requestMock
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->objectManagerMock
            ->method('get')
            ->withConsecutive([Data::class], [Data::class], [LoggerInterface::class])
            ->willReturnOnConsecutiveCalls($helper, $helper, $logger);

        $this->eventManagerMock
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Test name has been updated in your Wish List.', null)
            ->willThrowException($exception);
        $this->messageManagerMock
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with('We can\'t update your Wish List right now.', null)
            ->willReturn(true);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*', ['wishlist_id' => 56])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }
}
