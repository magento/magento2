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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Message\Manager;
use Magento\Framework\Url;
use Magento\Wishlist\Controller\Index\Remove;
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
    protected $productRepository;

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
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Event\Manager|MockObject
     */
    protected $eventManager;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var Validator|MockObject
     */
    protected $formKeyValidator;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->context = $this->createMock(Context::class);
        $this->request = $this->createMock(Http::class);
        $this->wishlistProvider = $this->createMock(WishlistProvider::class);
        $this->om = $this->createMock(ObjectManager::class);
        $this->messageManager = $this->createMock(Manager::class);
        $this->url = $this->createMock(Url::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
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
     * TearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset(
            $this->productRepository,
            $this->context,
            $this->request,
            $this->wishlistProvider,
            $this->om,
            $this->messageManager,
            $this->url,
            $this->eventManager
        );
    }

    /**
     * Prepare context
     *
     * @return void
     */
    public function prepareContext()
    {
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
            ->willReturn($this->eventManager);
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
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
    }

    /**
     * Get controller
     *
     * @return UpdateItemOptions
     */
    protected function getController()
    {
        $this->prepareContext();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        return new UpdateItemOptions(
            $this->context,
            $this->customerSession,
            $this->wishlistProvider,
            $this->productRepository,
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

        $controller = new Remove(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator
        );

        $this->assertSame($this->resultRedirectMock, $controller->execute());
    }

    /**
     * Test execute without product id
     *
     * @return void
     */
    public function testExecuteWithoutProductId()
    {
        $this->request
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
     * Test execute without product
     *
     * @return void
     */
    public function testExecuteWithoutProduct()
    {
        $this->request
            ->expects($this->once())
            ->method('getParam')
            ->with('product')
            ->willReturn(2);

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willThrowException(new NoSuchEntityException());

        $this->messageManager
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
     * Test execute without wish list
     *
     * @return void
     */
    public function testExecuteWithoutWishList()
    {
        $product = $this->createMock(Product::class);
        $item = $this->createMock(Item::class);

        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->willReturn(true);

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('product', null)
            ->willReturn(2);
        $this->request
            ->expects($this->at(1))
            ->method('getParam')
            ->with('id', null)
            ->willReturn(3);

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->willReturn($product);

        $this->messageManager
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

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn(null);

        $this->om
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
     * Test execute add success exception
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteAddSuccessException()
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

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('product', null)
            ->willReturn(2);
        $this->request
            ->expects($this->at(1))
            ->method('getParam')
            ->with('id', null)
            ->willReturn(3);

        $this->productRepository
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

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn($wishlist);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->om
            ->expects($this->exactly(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($helper);

        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManager
            ->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Test name has been updated in your Wish List.', null)
            ->willThrowException(new LocalizedException(__('error-message')));
        $this->messageManager
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
     * Test execute add success critical exception
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteAddSuccessCriticalException()
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
            ->with($exception)
            ->willReturn(true);

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

        $this->request
            ->expects($this->at(0))
            ->method('getParam')
            ->with('product', null)
            ->willReturn(2);
        $this->request
            ->expects($this->at(1))
            ->method('getParam')
            ->with('id', null)
            ->willReturn(3);

        $this->productRepository
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

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->with(12)
            ->willReturn($wishlist);

        $this->om
            ->expects($this->once())
            ->method('create')
            ->with(Item::class)
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->om
            ->expects($this->at(1))
            ->method('get')
            ->with(Data::class)
            ->willReturn($helper);
        $this->om
            ->expects($this->at(2))
            ->method('get')
            ->with(Data::class)
            ->willReturn($helper);
        $this->om
            ->expects($this->at(3))
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger);

        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManager
            ->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Test name has been updated in your Wish List.', null)
            ->willThrowException($exception);
        $this->messageManager
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
