<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends \PHPUnit_Framework_TestCase
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
        $this->resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultLayout = $this->getMockBuilder('Magento\Framework\View\Result\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirect],
                [ResultFactory::TYPE_LAYOUT, [], $this->resultLayout],
            ]);

        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->setMethods([
                'getPost',
                'getPostValue',
            ])
            ->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();

        $this->url = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->getMockForAbstractClass();

        $this->eventManager = $this->getMockBuilder('Magento\Framework\Event\ManagerInterface')
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
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

        $this->formKeyValidator = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProvider = $this->getMockBuilder('Magento\Wishlist\Controller\WishlistProviderInterface')
            ->getMockForAbstractClass();

        $this->wishlistConfig = $this->getMockBuilder('Magento\Wishlist\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transportBuilder = $this->getMockBuilder('Magento\Framework\Mail\Template\TransportBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->inlineTranslation = $this->getMockBuilder('Magento\Framework\Translate\Inline\StateInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerViewHelper = $this->getMockBuilder('Magento\Customer\Helper\View')
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistSession = $this->getMockBuilder('Magento\Framework\Session\Generic')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId'])
            ->getMock();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $this->wishlist = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
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

        $this->customerData = $this->getMockBuilder('Magento\Customer\Model\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->setMethods([
                'getBlock',
                'setWishlistId',
                'toHtml',
            ])
            ->getMock();

        $this->transport = $this->getMockBuilder('Magento\Framework\Mail\TransportInterface')
            ->getMockForAbstractClass();

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
            $this->storeManager
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

    /**
     * @param string $text
     * @param int $textLimit
     * @param string $emails
     * @param int $emailsLimit
     * @param int $shared
     * @param string $postValue
     * @param string $errorMessage
     *
     * @dataProvider dataProviderExecuteWithError
     */
    public function testExecuteWithError(
        $text,
        $textLimit,
        $emails,
        $emailsLimit,
        $shared,
        $postValue,
        $errorMessage
    ) {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->wishlist->expects($this->once())
            ->method('getShared')
            ->willReturn($shared);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($this->wishlist);

        $this->wishlistConfig->expects($this->once())
            ->method('getSharingEmailLimit')
            ->willReturn($emailsLimit);
        $this->wishlistConfig->expects($this->once())
            ->method('getSharingTextLimit')
            ->willReturn($textLimit);

        $this->request->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap([
                ['emails', $emails],
                ['message', $text],
            ]);
        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postValue);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($errorMessage)
            ->willReturnSelf();

        $this->wishlistSession->expects($this->any())
            ->method('setSharingForm')
            ->with($postValue)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * 1. Text
     * 2. Text limit
     * 3. Emails
     * 4. Emails limit
     * 5. Shared wishlists counter
     * 6. POST value
     * 7. Error message (RESULT)
     *
     * @return array
     */
    public function dataProviderExecuteWithError()
    {
        return [
            ['test text', 1, 'user1@example.com', 1, 0, '', 'Message length must not exceed 1 symbols'],
            ['test text', 100, null, 1, 0, '', 'Please enter an email address.'],
            ['test text', 100, '', 1, 0, '', 'Please enter an email address.'],
            ['test text', 100, 'user1@example.com', 1, 1, '', 'This wish list can be shared 0 more times.'],
            [
                'test text',
                100,
                'u1@example.com, u2@example.com',
                3,
                2,
                '',
                'This wish list can be shared 1 more times.'
            ],
            ['test text', 100, 'wrongEmailAddress', 1, 0, '', 'Please enter a valid email address.'],
            ['test text', 100, 'user1@example.com, wrongEmailAddress', 2, 0, '', 'Please enter a valid email address.'],
            ['test text', 100, 'wrongEmailAddress, user2@example.com', 2, 0, '', 'Please enter a valid email address.'],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithException()
    {
        $text = 'test text';
        $textLimit = 100;
        $emails = 'user1@example.com';
        $emailsLimit = 1;
        $shared = 0;
        $customerName = 'user1 user1';
        $wishlistId = 1;
        $rssLink = 'rss link';
        $sharingCode = 'sharing code';
        $exceptionMessage = 'test exception message';
        $postValue = '';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->wishlist->expects($this->exactly(2))
            ->method('getShared')
            ->willReturn($shared);
        $this->wishlist->expects($this->once())
            ->method('setShared')
            ->with($shared)
            ->willReturnSelf();
        $this->wishlist->expects($this->once())
            ->method('getId')
            ->willReturn($wishlistId);
        $this->wishlist->expects($this->once())
            ->method('getSharingCode')
            ->willReturn($sharingCode);
        $this->wishlist->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($this->wishlist);

        $this->wishlistConfig->expects($this->once())
            ->method('getSharingEmailLimit')
            ->willReturn($emailsLimit);
        $this->wishlistConfig->expects($this->once())
            ->method('getSharingTextLimit')
            ->willReturn($textLimit);

        $this->request->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap([
                ['emails', $emails],
                ['message', $text],
            ]);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->with('rss_url')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postValue);

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('wishlist.email.rss')
            ->willReturnSelf();
        $this->layout->expects($this->once())
            ->method('setWishlistId')
            ->with($wishlistId)
            ->willReturnSelf();
        $this->layout->expects($this->once())
            ->method('toHtml')
            ->willReturn($rssLink);

        $this->resultLayout->expects($this->exactly(2))
            ->method('addHandle')
            ->willReturnMap([
                ['wishlist_email_rss', null],
                ['wishlist_email_items', null],
            ]);
        $this->resultLayout->expects($this->once())
            ->method('getLayout')
            ->willReturn($this->layout);

        $this->inlineTranslation->expects($this->once())
            ->method('suspend')
            ->willReturnSelf();
        $this->inlineTranslation->expects($this->once())
            ->method('resume')
            ->willReturnSelf();

        $this->customerSession->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($this->customerData);

        $this->customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->with($this->customerData)
            ->willReturn($customerName);

        // Throw Exception
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->willThrowException(new \Exception($exceptionMessage));

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($exceptionMessage)
            ->willReturnSelf();

        $this->wishlistSession->expects($this->any())
            ->method('setSharingForm')
            ->with($postValue)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $text = 'text';
        $textLimit = 100;
        $emails = 'user1@example.com';
        $emailsLimit = 1;
        $shared = 0;
        $customerName = 'user1 user1';
        $wishlistId = 1;
        $sharingCode = 'sharing code';
        $templateIdentifier = 'template identifier';
        $storeId = 1;
        $viewOnSiteLink = 'view on site link';
        $from = 'user0@example.com';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->wishlist->expects($this->exactly(2))
            ->method('getShared')
            ->willReturn($shared);
        $this->wishlist->expects($this->once())
            ->method('setShared')
            ->with(++$shared)
            ->willReturnSelf();
        $this->wishlist->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($wishlistId);
        $this->wishlist->expects($this->once())
            ->method('getSharingCode')
            ->willReturn($sharingCode);
        $this->wishlist->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->wishlist->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($this->wishlist);

        $this->wishlistConfig->expects($this->once())
            ->method('getSharingEmailLimit')
            ->willReturn($emailsLimit);
        $this->wishlistConfig->expects($this->once())
            ->method('getSharingTextLimit')
            ->willReturn($textLimit);

        $this->request->expects($this->exactly(2))
            ->method('getPost')
            ->willReturnMap([
                ['emails', $emails],
                ['message', $text],
            ]);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->with('rss_url')
            ->willReturn(true);

        $this->layout->expects($this->exactly(2))
            ->method('getBlock')
            ->willReturnMap([
                ['wishlist.email.rss', $this->layout],
                ['wishlist.email.items', $this->layout],
            ]);

        $this->layout->expects($this->once())
            ->method('setWishlistId')
            ->with($wishlistId)
            ->willReturnSelf();
        $this->layout->expects($this->exactly(2))
            ->method('toHtml')
            ->willReturn($text);

        $this->resultLayout->expects($this->exactly(2))
            ->method('addHandle')
            ->willReturnMap([
                ['wishlist_email_rss', null],
                ['wishlist_email_items', null],
            ]);
        $this->resultLayout->expects($this->exactly(2))
            ->method('getLayout')
            ->willReturn($this->layout);

        $this->inlineTranslation->expects($this->once())
            ->method('suspend')
            ->willReturnSelf();
        $this->inlineTranslation->expects($this->once())
            ->method('resume')
            ->willReturnSelf();

        $this->customerSession->expects($this->once())
            ->method('getCustomerDataObject')
            ->willReturn($this->customerData);

        $this->customerViewHelper->expects($this->once())
            ->method('getCustomerName')
            ->with($this->customerData)
            ->willReturn($customerName);

        $this->scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                ['wishlist/email/email_template', ScopeInterface::SCOPE_STORE, null, $templateIdentifier],
                ['wishlist/email/email_identity', ScopeInterface::SCOPE_STORE, null, $from],
            ]);

        $this->store->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->url->expects($this->once())
            ->method('getUrl')
            ->with('*/shared/index', ['code' => $sharingCode])
            ->willReturn($viewOnSiteLink);

        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($templateIdentifier)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with([
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->with([
                'customer' => $this->customerData,
                'customerName' => $customerName,
                'salable' => 'yes',
                'items' => $text,
                'viewOnSiteLink' => $viewOnSiteLink,
                'message' => $text . $text,
                'store' => $this->store,
            ])
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setScopeId')
            ->with($storeId)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('setFrom')
            ->with($from)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($emails)
            ->willReturnSelf();
        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->willReturn($this->transport);

        $this->transport->expects($this->once())
            ->method('sendMessage')
            ->willReturnSelf();

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('wishlist_share', ['wishlist' => $this->wishlist])
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with(__('Your wish list has been shared.'))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*', ['wishlist_id' => $wishlistId])
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }
}
