<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\Generic as WishlistSession;
use Magento\Framework\Translate\Inline\StateInterface as TranslateInlineStateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\Index\Send;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Config as WishlistConfig;
use Magento\Wishlist\Model\Wishlist;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends \PHPUnit\Framework\TestCase
{
    /** @var  Send |\PHPUnit_Framework_MockObject_MockObject */
    protected $model;

    /** @var  ActionContext |\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var  FormKeyValidator |\PHPUnit_Framework_MockObject_MockObject */
    protected $formKeyValidator;

    /** @var  CustomerSession |\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSession;

    /** @var  WishlistProviderInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistProvider;

    /** @var  WishlistConfig |\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistConfig;

    /** @var  TransportBuilder |\PHPUnit_Framework_MockObject_MockObject */
    protected $transportBuilder;

    /** @var  TranslateInlineStateInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $inlineTranslation;

    /** @var  CustomerViewHelper |\PHPUnit_Framework_MockObject_MockObject */
    protected $customerViewHelper;

    /** @var  WishlistSession |\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlistSession;

    /** @var  ScopeConfigInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;

    /** @var  Store |\PHPUnit_Framework_MockObject_MockObject */
    protected $store;

    /** @var  StoreManagerInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var  ResultFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactory;

    /** @var  ResultRedirect |\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var  ResultLayout |\PHPUnit_Framework_MockObject_MockObject */
    protected $resultLayout;

    /** @var  Layout |\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    /** @var  RequestInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var  Wishlist |\PHPUnit_Framework_MockObject_MockObject */
    protected $wishlist;

    /** @var  ManagerInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var  CustomerData |\PHPUnit_Framework_MockObject_MockObject */
    protected $customerData;

    /** @var  UrlInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $url;

    /** @var  TransportInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $transport;

    /** @var  EventManagerInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManager;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultLayout = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirect],
                [ResultFactory::TYPE_LAYOUT, [], $this->resultLayout],
            ]);

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods([
                'getPost',
                'getPostValue',
            ])
            ->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->url = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();

        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->url);
        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManager);

        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProvider = $this->getMockBuilder(\Magento\Wishlist\Controller\WishlistProviderInterface::class)
            ->getMockForAbstractClass();

        $this->wishlistConfig = $this->getMockBuilder(\Magento\Wishlist\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transportBuilder = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inlineTranslation = $this->getMockBuilder(\Magento\Framework\Translate\Inline\StateInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerViewHelper = $this->getMockBuilder(\Magento\Customer\Helper\View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistSession = $this->getMockBuilder(\Magento\Framework\Session\Generic::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSharingForm'])
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->wishlist = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShared',
                'setShared',
                'getId',
                'getSharingCode',
                'save',
                'isSalable',
            ])
            ->getMock();

        $this->customerData = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getBlock',
                'setWishlistId',
                'toHtml',
            ])
            ->getMock();

        $this->transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->getMockForAbstractClass();

        $this->captchaHelper = $this->getMockBuilder(\Magento\Captcha\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getCaptcha'
            ])
            ->getMock();

        $this->captchaModel = $this->getMockBuilder(\Magento\Captcha\Model\DefaultModel::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isRequired'
            ])
            ->getMock();

        $this->captchaHelper->expects($this->once())->method('getCaptcha')
            ->willReturn($this->captchaModel);
        $this->captchaModel->expects($this->any())->method('isRequired')
            ->willReturn(false);

        $this->model = new Send(
            $this->context,
            $this->formKeyValidator,
            $this->customerSession,
            $this->wishlistProvider,
            $this->wishlistConfig,
            $this->transportBuilder,
            $this->inlineTranslation,
            $this->customerViewHelper,
            $this->wishlistSession,
            $this->scopeConfig,
            $this->storeManager,
            $this->captchaHelper
        );
    }

    public function testExecuteNoFormKeyValidated()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @expectedExceptionMessage Page not found.
     */
    public function testExecuteNoWishlistAvailable()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);

        $this->model->execute();
    }
}
