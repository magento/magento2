<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class AddTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Wishlist\Controller\Index\Add|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    public function setUp()
    {
        $this->context = $this->getMock(
            'Magento\Framework\App\Action\Context',
            [],
            [],
            '',
            false
        );
        $this->wishlistProvider = $this->getMock(
            'Magento\Wishlist\Controller\WishlistProvider',
            ['getWishlist'],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock(
            'Magento\Customer\Model\Session',
            [
                'getBeforeWishlistRequest',
                'unsBeforeWishlistRequest',
                'getBeforeWishlistUrl',
                'setAddActionReferer',
                'setBeforeWishlistUrl',
            ],
            [],
            '',
            false
        );
        $this->productRepository = $this->getMock(
            '\Magento\Catalog\Model\ProductRepository',
            [],
            [],
            '',
            false
        );
        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT, [])
            ->willReturn($this->resultRedirectMock);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function configureContext()
    {
        $om = $this->getMock(
            'Magento\Framework\App\ObjectManager',
            null,
            [],
            '',
            false
        );
        $request = $this->getMock(
            'Magento\Framework\App\Request\Http',
            null,
            [],
            '',
            false
        );
        $response = $this->getMock(
            'Magento\Framework\App\Response\Http',
            null,
            [],
            '',
            false
        );
        $eventManager = $this->getMock(
            'Magento\Framework\Event\Manager',
            null,
            [],
            '',
            false
        );
        $url = $this->getMock(
            'Magento\Framework\Url',
            null,
            [],
            '',
            false
        );
        $actionFlag = $this->getMock(
            'Magento\Framework\App\ActionFlag',
            null,
            [],
            '',
            false
        );
        $view = $this->getMock(
            'Magento\Framework\App\View',
            null,
            [],
            '',
            false
        );
        $messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            null,
            [],
            '',
            false
        );

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($om));
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
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

    public function configureCustomerSession()
    {
        $this->customerSession
            ->expects($this->exactly(2))
            ->method('getBeforeWishlistRequest')
            ->will($this->returnValue(false));
        $this->customerSession
            ->expects($this->once())
            ->method('unsBeforeWishlistRequest')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->once())
            ->method('getBeforeWishlistUrl')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->once())
            ->method('setAddActionReferer')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->once())
            ->method('setBeforeWishlistUrl')
            ->will($this->returnValue(null));
    }

    protected function createController()
    {
        $this->controller = new \Magento\Wishlist\Controller\Index\Add(
            $this->context,
            $this->customerSession,
            $this->wishlistProvider,
            $this->productRepository
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteWithoutWishList()
    {
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue(null));

        $this->configureContext();
        $this->createController();

        $this->controller->execute();
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutProductId()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', ['addNewItem', 'save', 'getId'], [], '', false);
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));


        $request = $this->getMock('Magento\Framework\App\Request\Http', ['getParams'], [], '', false);
        $request
            ->expects($this->once())
            ->method('getParams')
            ->will($this->returnValue([]));

        $om = $this->getMock('Magento\Framework\App\ObjectManager', null, [], '', false);
        $response = $this->getMock('Magento\Framework\App\Response\Http', null, [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', null, [], '', false);
        $url = $this->getMock('Magento\Framework\Url', null, [], '', false);
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', null, [], '', false);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/', [])
            ->willReturnSelf();
        $view = $this->getMock('Magento\Framework\App\View', null, [], '', false);
        $messageManager = $this->getMock('Magento\Framework\Message\Manager', null, [], '', false);

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($om));
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
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
            ->method('getView')
            ->will($this->returnValue($view));
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));

        $this->customerSession
            ->expects($this->exactly(2))
            ->method('getBeforeWishlistRequest')
            ->will($this->returnValue(true));
        $this->customerSession
            ->expects($this->once())
            ->method('unsBeforeWishlistRequest')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('getBeforeWishlistUrl')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('setAddActionReferer')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('setBeforeWishlistUrl')
            ->will($this->returnValue(null));
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->createController();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithProductIdAndWithoutProduct()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', ['addNewItem', 'save', 'getId'], [], '', false);
        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));

        $request = $this->getMock('Magento\Framework\App\Request\Http', ['getParams'], [], '', false);
        $request
            ->expects($this->once())
            ->method('getParams')
            ->will($this->returnValue(['product' => 2]));

        $om = $this->getMock('Magento\Framework\App\ObjectManager', null, [], '', false);
        $response = $this->getMock('Magento\Framework\App\Response\Http', null, [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', null, [], '', false);
        $url = $this->getMock('Magento\Framework\Url', null, [], '', false);
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', null, [], '', false);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/', [])
            ->willReturnSelf();
        $view = $this->getMock('Magento\Framework\App\View', null, [], '', false);
        $messageManager = $this->getMock('Magento\Framework\Message\Manager', ['addError'], [], '', false);
        $messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t specify a product.')
            ->will($this->returnValue(null));

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($om));
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
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
            ->method('getView')
            ->will($this->returnValue($view));
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->customerSession
            ->expects($this->exactly(1))
            ->method('getBeforeWishlistRequest')
            ->will($this->returnValue(false));
        $this->customerSession
            ->expects($this->never())
            ->method('unsBeforeWishlistRequest')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('getBeforeWishlistUrl')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('setAddActionReferer')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('setBeforeWishlistUrl')
            ->will($this->returnValue(null));

        $this->createController();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithProductAndCantAddProductToWishlist()
    {
        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', ['addNewItem', 'save', 'getId'], [], '', false);
        $wishlist
            ->expects($this->once())
            ->method('addNewItem')
            ->will($this->returnValue('Can\'t add product to Wish List'));

        $wishlist
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));


        $request = $this->getMock('Magento\Framework\App\Request\Http', ['getParams'], [], '', false);
        $request
            ->expects($this->once())
            ->method('getParams')
            ->will($this->returnValue(['product' => 2]));

        $om = $this->getMock('Magento\Framework\App\ObjectManager', null, [], '', false);
        $response = $this->getMock('Magento\Framework\App\Response\Http', null, [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', null, [], '', false);
        $url = $this->getMock('Magento\Framework\Url', null, [], '', false);
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', null, [], '', false);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*', ['wishlist_id' => 2])
            ->willReturnSelf();

        $view = $this->getMock('Magento\Framework\App\View', null, [], '', false);
        $messageManager = $this->getMock('Magento\Framework\Message\Manager', ['addError'], [], '', false);
        $messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t add the item to Wish List right now: Can\'t add product to Wish List.')
            ->will($this->returnValue(null));

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($om));
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
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
            ->method('getView')
            ->will($this->returnValue($view));
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->customerSession
            ->expects($this->exactly(1))
            ->method('getBeforeWishlistRequest')
            ->will($this->returnValue(false));
        $this->customerSession
            ->expects($this->never())
            ->method('unsBeforeWishlistRequest')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('getBeforeWishlistUrl')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('setAddActionReferer')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->never())
            ->method('setBeforeWishlistUrl')
            ->will($this->returnValue(null));

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['isVisibleInCatalog'],
            [],
            '',
            false
        );
        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->will($this->returnValue(true));

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->will($this->returnValue($product));

        $this->createController();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteProductAddedToWishlistAfterObjectManagerThrowException()
    {
        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['isVisibleInCatalog', 'getName'],
            [],
            '',
            false
        );
        $product
            ->expects($this->once())
            ->method('isVisibleInCatalog')
            ->will($this->returnValue(true));
        $product
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Product test name'));

        $this->productRepository
            ->expects($this->once())
            ->method('getById')
            ->with(2)
            ->will($this->returnValue($product));

        $exception = new \Exception('Exception');
        $wishListItem = new \stdClass();

        $wishlist = $this->getMock('Magento\Wishlist\Model\Wishlist', ['addNewItem', 'save', 'getId'], [], '', false);
        $wishlist
            ->expects($this->once())
            ->method('addNewItem')
            ->will($this->returnValue($wishListItem));

        $wishlist
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));

        $wishlist
            ->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $this->wishlistProvider
            ->expects($this->once())
            ->method('getWishlist')
            ->will($this->returnValue($wishlist));


        $request = $this->getMock('Magento\Framework\App\Request\Http', ['getParams'], [], '', false);
        $request
            ->expects($this->once())
            ->method('getParams')
            ->will($this->returnValue(['product' => 2]));

        $wishlistHelper = $this->getMock('Magento\Wishlist\Helper\Data', ['calculate'], [], '', false);
        $wishlistHelper
            ->expects($this->once())
            ->method('calculate')
            ->will($this->returnSelf());

        $escaper = $this->getMock('Magento\Framework\Escaper', ['escapeHtml', 'escapeUrl'], [], '', false);
        $escaper
            ->expects($this->once())
            ->method('escapeHtml')
            ->with('Product test name')
            ->will($this->returnValue('Product test name'));
        $escaper
            ->expects($this->once())
            ->method('escapeUrl')
            ->with('http://test-url.com')
            ->will($this->returnValue('http://test-url.com'));

        $logger = $this->getMock(
            'Magento\Framework\Logger\Monolog',
            ['critical'],
            [],
            '',
            false
        );
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->will($this->returnValue(true));

        $om = $this->getMock('Magento\Framework\App\ObjectManager', ['get'], [], '', false);
        $om
            ->expects($this->at(0))
            ->method('get')
            ->with('Magento\Wishlist\Helper\Data')
            ->will($this->returnValue($wishlistHelper));
        $om
            ->expects($this->at(1))
            ->method('get')
            ->with('Magento\Framework\Escaper')
            ->will($this->returnValue($escaper));
        $om
            ->expects($this->at(2))
            ->method('get')
            ->with('Magento\Framework\Escaper')
            ->will($this->returnValue($escaper));
        $om
            ->expects($this->at(3))
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->will($this->returnValue($logger));

        $response = $this->getMock('Magento\Framework\App\Response\Http', null, [], '', false);
        $eventManager = $this->getMock('Magento\Framework\Event\Manager', ['dispatch'], [], '', false);
        $eventManager
            ->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_add_product', ['wishlist' => $wishlist, 'product' => $product, 'item' => $wishListItem])
            ->will($this->returnValue(true));

        $url = $this->getMock('Magento\Framework\Url', null, [], '', false);
        $actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', null, [], '', false);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*', ['wishlist_id' => 2])
            ->willReturnSelf();

        $view = $this->getMock('Magento\Framework\App\View', null, [], '', false);

        $messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addError', 'addSuccess'],
            [],
            '',
            false
        );
        $messageManager
            ->expects($this->once())
            ->method('addError')
            ->with('We can\'t add the item to Wish List right now.')
            ->will($this->returnValue(null));
        $messageManager
            ->expects($this->once())
            ->method('addSuccess')
            ->will($this->throwException($exception));

        $this->context
            ->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($om));
        $this->context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
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
            ->method('getView')
            ->will($this->returnValue($view));
        $this->context
            ->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($messageManager));
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->customerSession
            ->expects($this->exactly(1))
            ->method('getBeforeWishlistRequest')
            ->will($this->returnValue(false));
        $this->customerSession
            ->expects($this->never())
            ->method('unsBeforeWishlistRequest')
            ->will($this->returnValue(null));
        $this->customerSession
            ->expects($this->once())
            ->method('getBeforeWishlistUrl')
            ->will($this->returnValue('http://test-url.com'));
        $this->customerSession
            ->expects($this->once())
            ->method('setBeforeWishlistUrl')
            ->with(null)
            ->will($this->returnValue(null));

        $this->createController();

        $this->assertSame($this->resultRedirectMock, $this->controller->execute());
    }
}
