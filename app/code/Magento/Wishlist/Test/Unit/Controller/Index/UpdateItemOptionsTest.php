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
class UpdateItemOptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

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
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

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

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productRepository = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $this->context = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->wishlistProvider = $this->createMock(\Magento\Wishlist\Controller\WishlistProvider::class);
        $this->om = $this->createMock(\Magento\Framework\App\ObjectManager::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->url = $this->createMock(\Magento\Framework\Url::class);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
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

    /**
     * TearDown method
     *
     * @return void
     */
    public function tearDown()
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
        $actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);

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
     * @return \Magento\Wishlist\Controller\Index\UpdateItemOptions
     */
    protected function getController()
    {
        $this->prepareContext();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        return new \Magento\Wishlist\Controller\Index\UpdateItemOptions(
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

        $controller = new \Magento\Wishlist\Controller\Index\Remove(
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
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->messageManager
            ->expects($this->once())
            ->method('addError')
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
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $item = $this->createMock(\Magento\Wishlist\Model\Item::class);

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
            ->method('addError')
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
            ->with(\Magento\Wishlist\Model\Item::class)
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
        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $item = $this->createMock(\Magento\Wishlist\Model\Item::class);
        $helper = $this->createMock(\Magento\Wishlist\Helper\Data::class);

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
            ->with(3, new \Magento\Framework\DataObject([]))
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
            ->with(\Magento\Wishlist\Model\Item::class)
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->om
            ->expects($this->exactly(2))
            ->method('get')
            ->with(\Magento\Wishlist\Helper\Data::class)
            ->willReturn($helper);

        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManager
            ->expects($this->once())
            ->method('addSuccess')
            ->with('Test name has been updated in your Wish List.', null)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('error-message')));
        $this->messageManager
            ->expects($this->once())
            ->method('addError')
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
        $wishlist = $this->createMock(\Magento\Wishlist\Model\Wishlist::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $item = $this->createMock(\Magento\Wishlist\Model\Item::class);
        $helper = $this->createMock(\Magento\Wishlist\Helper\Data::class);
        $logger = $this->createMock(\Magento\Framework\Logger\Monolog::class);
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
            ->with(3, new \Magento\Framework\DataObject([]))
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
            ->with(\Magento\Wishlist\Model\Item::class)
            ->willReturn($item);

        $this->request
            ->expects($this->once())
            ->method('getParams')
            ->willReturn([]);

        $this->om
            ->expects($this->at(1))
            ->method('get')
            ->with(\Magento\Wishlist\Helper\Data::class)
            ->willReturn($helper);
        $this->om
            ->expects($this->at(2))
            ->method('get')
            ->with(\Magento\Wishlist\Helper\Data::class)
            ->willReturn($helper);
        $this->om
            ->expects($this->at(3))
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($logger);

        $this->eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_update_item', ['wishlist' => $wishlist, 'product' => $product, 'item' => $item])
            ->willReturn(true);

        $this->messageManager
            ->expects($this->once())
            ->method('addSuccess')
            ->with('Test name has been updated in your Wish List.', null)
            ->willThrowException($exception);
        $this->messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t update your Wish List right now.', null)
            ->willReturn(true);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*', ['wishlist_id' => 56])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->getController()->execute());
    }
}
