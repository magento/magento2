<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Session\Generic as WishlistSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\Index\Send;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Config as WishlistConfig;
use Magento\Wishlist\Model\Validator\MessageValidator;
use Magento\Wishlist\Model\Wishlist;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var  Send|MockObject
     */
    protected $model;

    /**
     * @var  ActionContext|MockObject
     */
    protected $context;

    /**
     * @var  FormKeyValidator|MockObject
     */
    protected $formKeyValidator;

    /**
     * @var  WishlistProviderInterface|MockObject
     */
    protected $wishlistProvider;

    /**
     * @var  Store|MockObject
     */
    protected $store;

    /**
     * @var  ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var  ResultRedirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var  ResultLayout|MockObject
     */
    protected $resultLayout;

    /**
     * @var  RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var  ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var  CustomerData|MockObject
     */
    protected $customerData;

    /**
     * @var  UrlInterface|MockObject
     */
    protected $url;

    /**
     * @var  TransportInterface|MockObject
     */
    protected $transport;

    /**
     * @var  EventManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var  CaptchaHelper|MockObject
     */
    protected $captchaHelper;

    /**
     * @var CaptchaModel|MockObject
     */
    protected $captchaModel;

    /**
     * @var Session|MockObject
     */
    protected $customerSession;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->resultRedirect = $this->createMock(ResultRedirect::class);

        $this->resultLayout = $this->createMock(ResultLayout::class);

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirect],
                [ResultFactory::TYPE_LAYOUT, [], $this->resultLayout],
            ]);

        $this->request = $this->createPartialMock(RequestHttp::class, ['getPost', 'getPostValue', 'getParam']);

        $this->messageManager = $this->createMock(ManagerInterface::class);

        $this->url = $this->createMock(UrlInterface::class);

        $this->eventManager = $this->createMock(EventManagerInterface::class);

        $this->context = $this->createMock(ActionContext::class);
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

        $this->formKeyValidator = $this->createMock(FormKeyValidator::class);

        $customerMock = $this->createPartialMockWithReflection(
            Customer::class,
            ['getId', 'getEmail']
        );

        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn('expamle@mail.com');

        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomer', 'getData']);

        $this->customerSession->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $this->customerSession->expects($this->any())
            ->method('getData')
            ->willReturn(false);

        $this->wishlistProvider = $this->createMock(WishlistProviderInterface::class);

        $this->captchaHelper = $this->createPartialMock(CaptchaHelper::class, ['getCaptcha']);

        $this->captchaModel = $this->createPartialMock(CaptchaModel::class, ['isRequired', 'logAttempt']);

        $objectHelper = new ObjectManager($this);

        $this->captchaHelper->expects($this->any())->method('getCaptcha')
            ->willReturn($this->captchaModel);
        $this->captchaModel->expects($this->any())->method('isRequired')
            ->willReturn(false);

        $this->model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'wishlistProvider' => $this->wishlistProvider,
                'captchaHelper' => $this->captchaHelper,
                '_customerSession' => $this->customerSession
            ]
        );
    }

    /**
     * Verify execute method without Form Key validated
     *
     * @return void
     */
    public function testExecuteNoFormKeyValidated(): void
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
     * Verify execute with no emails left
     *
     * @return void
     */
    public function testExecuteWithNoEmailLeft(): void
    {
        $expectedMessage = new Phrase('Maximum of %1 emails can be sent.', [0]);

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->request
            ->method('getPost')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'emails') {
                    return 'some.email2@gmail.com';
                } elseif ($arg == 'message') {
                    return null;
                }
            });

        $wishlist = $this->createMock(Wishlist::class);
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with($expectedMessage);

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * Execute method with no wishlist available.
     *
     * @return void
     */
    public function testExecuteNoWishlistAvailable(): void
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Page not found');

        $this->model->execute();
    }

    /**
     * Test execute with invalid message content
     *
     * @return void
     */
    public function testExecuteWithInvalidMessageContent(): void
    {
        $maliciousMessage = '{{var this.getTemplateFilter()}}';
        
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlist = $this->createMock(Wishlist::class);
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->request
            ->method('getPost')
            ->willReturnCallback(function ($arg) use ($maliciousMessage) {
                if ($arg == 'emails') {
                    return 'test@example.com';
                } elseif ($arg == 'message') {
                    return $maliciousMessage;
                }
            });

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * Test execute with message length exceeding limit
     *
     * @return void
     */
    public function testExecuteWithMessageLengthExceeded(): void
    {
        $longMessage = str_repeat('a', 10001);
        
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlist = $this->createPartialMockWithReflection(Wishlist::class, ['getShared']);
        $wishlist->expects($this->any())->method('getShared')->willReturn(0);
        
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->request
            ->method('getPost')
            ->willReturnCallback(function ($arg) use ($longMessage) {
                if ($arg == 'emails') {
                    return 'test@example.com';
                } elseif ($arg == 'message') {
                    return $longMessage;
                }
            });

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * Test execute with empty emails
     *
     * @return void
     */
    public function testExecuteWithEmptyEmails(): void
    {
        // Create WishlistConfig mock to prevent length validation errors
        $wishlistConfig = $this->createMock(WishlistConfig::class);
        $wishlistConfig->method('getSharingEmailLimit')->willReturn(10);
        $wishlistConfig->method('getSharingTextLimit')->willReturn(255);
        
        $escaper = $this->createMock(Escaper::class);
        $escaper->method('escapeHtml')->willReturnArgument(0);
        
        $messageValidator = $this->createMock(MessageValidator::class);
        $messageValidator->method('isValid')->willReturn(true);

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'wishlistProvider' => $this->wishlistProvider,
                'wishlistConfig' => $wishlistConfig,
                'captchaHelper' => $this->captchaHelper,
                '_customerSession' => $this->customerSession,
                'escaper' => $escaper,
                'messageValidator' => $messageValidator
            ]
        );

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlist = $this->createPartialMockWithReflection(Wishlist::class, ['getShared']);
        $wishlist->expects($this->any())->method('getShared')->willReturn(0);
        
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->request
            ->method('getPost')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'emails') {
                    return '';
                } elseif ($arg == 'message') {
                    return 'Test message';
                }
            });

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(new Phrase('Please enter an email address.'));

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $model->execute());
    }

    /**
     * Test execute with invalid email format
     *
     * @return void
     */
    public function testExecuteWithInvalidEmail(): void
    {
        // Create WishlistConfig mock to prevent length validation errors
        $wishlistConfig = $this->createMock(WishlistConfig::class);
        $wishlistConfig->method('getSharingEmailLimit')->willReturn(10);
        $wishlistConfig->method('getSharingTextLimit')->willReturn(255);
        
        $escaper = $this->createMock(Escaper::class);
        $escaper->method('escapeHtml')->willReturnArgument(0);
        
        $messageValidator = $this->createMock(MessageValidator::class);
        $messageValidator->method('isValid')->willReturn(true);

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'wishlistProvider' => $this->wishlistProvider,
                'wishlistConfig' => $wishlistConfig,
                'captchaHelper' => $this->captchaHelper,
                '_customerSession' => $this->customerSession,
                'escaper' => $escaper,
                'messageValidator' => $messageValidator
            ]
        );

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlist = $this->createPartialMockWithReflection(Wishlist::class, ['getShared']);
        $wishlist->expects($this->any())->method('getShared')->willReturn(0);
        
        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlist);

        $this->request
            ->method('getPost')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'emails') {
                    return 'invalid-email';
                } elseif ($arg == 'message') {
                    return 'Test message';
                }
            });

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(new Phrase('Please enter a valid email address.'));

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $model->execute());
    }

    /**
     * Test execute with incorrect CAPTCHA
     *
     * @return void
     */
    public function testExecuteWithIncorrectCaptcha(): void
    {
        $captchaModel = $this->createPartialMock(CaptchaModel::class, ['isRequired', 'isCorrect', 'logAttempt']);

        $captchaHelper = $this->createPartialMock(CaptchaHelper::class, ['getCaptcha']);

        $captchaHelper->expects($this->once())
            ->method('getCaptcha')
            ->willReturn($captchaModel);

        $captchaModel->expects($this->any())
            ->method('isRequired')
            ->willReturn(true);

        $captchaModel->expects($this->once())
            ->method('isCorrect')
            ->willReturn(false);

        $captchaModel->expects($this->once())
            ->method('logAttempt');

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'wishlistProvider' => $this->wishlistProvider,
                'captchaHelper' => $captchaHelper,
                '_customerSession' => $this->customerSession
            ]
        );

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(new Phrase('Incorrect CAPTCHA'));

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $model->execute());
    }

    /**
     * Test successful wishlist send
     *
     * @return void
     * @suppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteSuccess(): void
    {
        // Setup all required mocks
        $wishlistConfig = $this->createMock(WishlistConfig::class);
        $wishlistConfig->method('getSharingEmailLimit')->willReturn(10);
        $wishlistConfig->method('getSharingTextLimit')->willReturn(255);

        $transportBuilder = $this->createMock(TransportBuilder::class);
        $transport = $this->createMock(TransportInterface::class);
        $inlineTranslation = $this->createMock(StateInterface::class);
        $customerHelper = $this->createMock(CustomerViewHelper::class);
        $wishlistSession = $this->createMock(WishlistSession::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createPartialMockWithReflection(Store::class, ['getStoreId']);
        $escaper = $this->createMock(Escaper::class);
        $messageValidator = $this->createMock(MessageValidator::class);

        $customerData = $this->createMock(CustomerData::class);
        
        $customerModel = $this->createPartialMockWithReflection(Customer::class, ['getId', 'getEmail']);
        $customerModel->method('getId')->willReturn(null);
        $customerModel->method('getEmail')->willReturn('');
        
        $session = $this->createMock(Session::class);
        $session->method('getCustomerDataObject')->willReturn($customerData);
        $session->method('getCustomer')->willReturn($customerModel);

        $customerHelper->method('getCustomerName')->willReturn('John Doe');
        $escaper->method('escapeHtml')->willReturnArgument(0);
        $messageValidator->method('isValid')->willReturn(true);
        
        $store->method('getStoreId')->willReturn(1);
        $storeManager->method('getStore')->willReturn($store);
        $scopeConfig->method('getValue')->willReturn('template_id');

        $layout = $this->createMock(LayoutInterface::class);
        $block = $this->createPartialMockWithReflection(AbstractBlock::class, ['setWishlistId', 'toHtml']);
        $block->method('toHtml')->willReturn('<html>test</html>');
        $block->method('setWishlistId')->willReturnSelf();
        $layout->method('getBlock')->willReturn($block);
        $this->resultLayout->method('getLayout')->willReturn($layout);
        $this->resultLayout->method('addHandle')->willReturnSelf();

        $transportBuilder->method('setTemplateIdentifier')->willReturnSelf();
        $transportBuilder->method('setTemplateOptions')->willReturnSelf();
        $transportBuilder->method('setTemplateVars')->willReturnSelf();
        $transportBuilder->method('setFrom')->willReturnSelf();
        $transportBuilder->method('addTo')->willReturnSelf();
        $transportBuilder->method('getTransport')->willReturn($transport);

        $transport->expects($this->once())->method('sendMessage');

        $wishlist = $this->createPartialMockWithReflection(
            Wishlist::class,
            ['getShared', 'getSharingCode', 'getId', 'save', 'isSalable']
        );
        $wishlist->method('getShared')->willReturn(0);
        $wishlist->method('getId')->willReturn(1);
        $wishlist->method('getSharingCode')->willReturn('abc123');
        $wishlist->method('isSalable')->willReturn(true);
        $wishlist->expects($this->once())->method('save');

        $this->wishlistProvider->method('getWishlist')->willReturn($wishlist);

        $this->formKeyValidator->method('validate')->willReturn(true);

        $this->request->method('getPost')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'emails') {
                    return 'test@example.com';
                } elseif ($arg == 'message') {
                    return 'Test message';
                }
            });

        $this->request->method('getParam')->willReturn(null);

        $inlineTranslation->expects($this->once())->method('suspend');
        $inlineTranslation->expects($this->once())->method('resume');

        $this->eventManager->expects($this->once())->method('dispatch')->with('wishlist_share');
        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*', ['wishlist_id' => 1])
            ->willReturnSelf();

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'customerSession' => $session,
                'wishlistProvider' => $this->wishlistProvider,
                'wishlistConfig' => $wishlistConfig,
                'transportBuilder' => $transportBuilder,
                'inlineTranslation' => $inlineTranslation,
                'customerHelperView' => $customerHelper,
                'wishlistSession' => $wishlistSession,
                'scopeConfig' => $scopeConfig,
                'storeManager' => $storeManager,
                'captchaHelper' => $this->captchaHelper,
                'escaper' => $escaper,
                'messageValidator' => $messageValidator
            ]
        );

        $this->assertEquals($this->resultRedirect, $model->execute());
    }

    /**
     * Test successful wishlist send with RSS link
     *
     * @return void
     * @suppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteSuccessWithRssLink(): void
    {
        $wishlistConfig = $this->createMock(WishlistConfig::class);
        $wishlistConfig->method('getSharingEmailLimit')->willReturn(10);
        $wishlistConfig->method('getSharingTextLimit')->willReturn(255);

        $transportBuilder = $this->createMock(TransportBuilder::class);
        $transport = $this->createMock(TransportInterface::class);
        $inlineTranslation = $this->createMock(StateInterface::class);
        $customerHelper = $this->createMock(CustomerViewHelper::class);
        $wishlistSession = $this->createMock(WishlistSession::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createPartialMockWithReflection(Store::class, ['getStoreId']);
        $escaper = $this->createMock(Escaper::class);
        $messageValidator = $this->createMock(MessageValidator::class);

        $customerData = $this->createMock(CustomerData::class);
        
        $customerModel = $this->createPartialMockWithReflection(Customer::class, ['getId', 'getEmail']);
        $customerModel->method('getId')->willReturn(null);
        $customerModel->method('getEmail')->willReturn('');
        
        $session = $this->createMock(Session::class);
        $session->method('getCustomerDataObject')->willReturn($customerData);
        $session->method('getCustomer')->willReturn($customerModel);

        $customerHelper->method('getCustomerName')->willReturn('John Doe');
        $escaper->method('escapeHtml')->willReturnArgument(0);
        $messageValidator->method('isValid')->willReturn(true);
        
        $store->method('getStoreId')->willReturn(1);
        $storeManager->method('getStore')->willReturn($store);
        $scopeConfig->method('getValue')->willReturn('template_id');

        $layout = $this->createMock(LayoutInterface::class);
        $block = $this->createPartialMockWithReflection(AbstractBlock::class, ['setWishlistId', 'toHtml']);
        $block->method('toHtml')->willReturn('<html>RSS Link</html>');
        $block->method('setWishlistId')->willReturnSelf();
        $layout->method('getBlock')->willReturn($block);
        $this->resultLayout->method('getLayout')->willReturn($layout);
        $this->resultLayout->method('addHandle')->willReturnSelf();

        $transportBuilder->method('setTemplateIdentifier')->willReturnSelf();
        $transportBuilder->method('setTemplateOptions')->willReturnSelf();
        $transportBuilder->method('setTemplateVars')->willReturnSelf();
        $transportBuilder->method('setFrom')->willReturnSelf();
        $transportBuilder->method('addTo')->willReturnSelf();
        $transportBuilder->method('getTransport')->willReturn($transport);

        $transport->expects($this->once())->method('sendMessage');

        $wishlist = $this->createPartialMockWithReflection(
            Wishlist::class,
            ['getShared', 'getSharingCode', 'getId', 'save', 'isSalable']
        );
        $wishlist->method('getShared')->willReturn(0);
        $wishlist->method('getId')->willReturn(1);
        $wishlist->method('getSharingCode')->willReturn('abc123');
        $wishlist->method('isSalable')->willReturn(true);
        $wishlist->expects($this->once())->method('save');

        $this->wishlistProvider->method('getWishlist')->willReturn($wishlist);
        $this->formKeyValidator->method('validate')->willReturn(true);

        $this->request->method('getPost')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'emails') {
                    return 'test@example.com';
                } elseif ($arg == 'message') {
                    return 'Test message';
                }
            });

        // Test with RSS URL parameter
        $this->request->method('getParam')->with('rss_url')->willReturn('1');

        $inlineTranslation->expects($this->once())->method('suspend');
        $inlineTranslation->expects($this->once())->method('resume');

        $this->eventManager->expects($this->once())->method('dispatch');
        $this->messageManager->expects($this->once())->method('addSuccessMessage');

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*', ['wishlist_id' => 1])
            ->willReturnSelf();

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'customerSession' => $session,
                'wishlistProvider' => $this->wishlistProvider,
                'wishlistConfig' => $wishlistConfig,
                'transportBuilder' => $transportBuilder,
                'inlineTranslation' => $inlineTranslation,
                'customerHelperView' => $customerHelper,
                'wishlistSession' => $wishlistSession,
                'scopeConfig' => $scopeConfig,
                'storeManager' => $storeManager,
                'captchaHelper' => $this->captchaHelper,
                'escaper' => $escaper,
                'messageValidator' => $messageValidator
            ]
        );

        $this->assertEquals($this->resultRedirect, $model->execute());
    }

    /**
     * Test exception during email sending
     *
     * @return void
     * @suppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteExceptionDuringSend(): void
    {
        $wishlistConfig = $this->createMock(WishlistConfig::class);
        $wishlistConfig->method('getSharingEmailLimit')->willReturn(10);
        $wishlistConfig->method('getSharingTextLimit')->willReturn(255);

        $transportBuilder = $this->createMock(TransportBuilder::class);
        $transport = $this->createMock(TransportInterface::class);
        $inlineTranslation = $this->createMock(StateInterface::class);
        $customerHelper = $this->createMock(CustomerViewHelper::class);
        $wishlistSession = $this->createPartialMockWithReflection(WishlistSession::class, ['setSharingForm']);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $store = $this->createPartialMockWithReflection(Store::class, ['getStoreId']);
        $escaper = $this->createMock(Escaper::class);
        $messageValidator = $this->createMock(MessageValidator::class);

        $customerData = $this->createMock(CustomerData::class);
        
        $customerModel = $this->createPartialMockWithReflection(Customer::class, ['getId', 'getEmail']);
        $customerModel->method('getId')->willReturn(null);
        $customerModel->method('getEmail')->willReturn('');
        
        $session = $this->createMock(Session::class);
        $session->method('getCustomerDataObject')->willReturn($customerData);
        $session->method('getCustomer')->willReturn($customerModel);

        $customerHelper->method('getCustomerName')->willReturn('John Doe');
        $escaper->method('escapeHtml')->willReturnArgument(0);
        $messageValidator->method('isValid')->willReturn(true);
        
        $store->method('getStoreId')->willReturn(1);
        $storeManager->method('getStore')->willReturn($store);
        $scopeConfig->method('getValue')->willReturn('template_id');

        $layout = $this->createMock(LayoutInterface::class);
        $block = $this->createPartialMockWithReflection(AbstractBlock::class, ['setWishlistId', 'toHtml']);
        $block->method('toHtml')->willReturn('<html>test</html>');
        $block->method('setWishlistId')->willReturnSelf();
        $layout->method('getBlock')->willReturn($block);
        $this->resultLayout->method('getLayout')->willReturn($layout);
        $this->resultLayout->method('addHandle')->willReturnSelf();

        $transportBuilder->method('setTemplateIdentifier')->willReturnSelf();
        $transportBuilder->method('setTemplateOptions')->willReturnSelf();
        $transportBuilder->method('setTemplateVars')->willReturnSelf();
        $transportBuilder->method('setFrom')->willReturnSelf();
        $transportBuilder->method('addTo')->willReturnSelf();
        $transportBuilder->method('getTransport')->willReturn($transport);

        // Simulate exception during send
        $transport->method('sendMessage')->willThrowException(new \Exception('Email sending failed'));

        $wishlist = $this->createPartialMockWithReflection(
            Wishlist::class,
            ['getShared', 'getSharingCode', 'getId', 'save', 'isSalable']
        );
        $wishlist->method('getShared')->willReturn(0);
        $wishlist->method('getId')->willReturn(1);
        $wishlist->method('getSharingCode')->willReturn('abc123');
        $wishlist->method('isSalable')->willReturn(true);
        $wishlist->expects($this->once())->method('save');

        $this->wishlistProvider->method('getWishlist')->willReturn($wishlist);
        $this->formKeyValidator->method('validate')->willReturn(true);

        $this->request->method('getPost')
            ->willReturnCallback(function ($arg) {
                if ($arg == 'emails') {
                    return 'test@example.com';
                } elseif ($arg == 'message') {
                    return 'Test message';
                }
            });

        $this->request->method('getParam')->willReturn(null);
        $this->request->method('getPostValue')->willReturn([]);

        $inlineTranslation->expects($this->once())->method('suspend');
        $inlineTranslation->expects($this->once())->method('resume');

        $this->messageManager->expects($this->once())->method('addErrorMessage')
            ->with('Email sending failed');

        $wishlistSession->expects($this->once())->method('setSharingForm');

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'customerSession' => $session,
                'wishlistProvider' => $this->wishlistProvider,
                'wishlistConfig' => $wishlistConfig,
                'transportBuilder' => $transportBuilder,
                'inlineTranslation' => $inlineTranslation,
                'customerHelperView' => $customerHelper,
                'wishlistSession' => $wishlistSession,
                'scopeConfig' => $scopeConfig,
                'storeManager' => $storeManager,
                'captchaHelper' => $this->captchaHelper,
                'escaper' => $escaper,
                'messageValidator' => $messageValidator
            ]
        );

        $this->assertEquals($this->resultRedirect, $model->execute());
    }

    /**
     * Test CAPTCHA logging with customer having ID
     *
     * @return void
     */
    public function testCaptchaLogAttemptWithCustomerId(): void
    {
        $customerMock = $this->createPartialMockWithReflection(Customer::class, ['getId', 'getEmail']);

        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn('customer@example.com');

        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn(123); // Customer has ID

        $customerSession = $this->createPartialMock(Session::class, ['getCustomer']);

        $customerSession->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $captchaModel = $this->createPartialMock(CaptchaModel::class, ['isRequired', 'isCorrect', 'logAttempt']);

        $captchaHelper = $this->createPartialMock(CaptchaHelper::class, ['getCaptcha']);

        $captchaHelper->expects($this->once())
            ->method('getCaptcha')
            ->willReturn($captchaModel);

        $captchaModel->expects($this->any())
            ->method('isRequired')
            ->willReturn(true);

        $captchaModel->expects($this->once())
            ->method('isCorrect')
            ->willReturn(false);

        // Verify logAttempt is called with customer email
        $captchaModel->expects($this->once())
            ->method('logAttempt')
            ->with('customer@example.com');

        $objectHelper = new ObjectManager($this);
        $model = $objectHelper->getObject(
            Send::class,
            [
                'context' => $this->context,
                'formKeyValidator' => $this->formKeyValidator,
                'wishlistProvider' => $this->wishlistProvider,
                'captchaHelper' => $captchaHelper,
                '_customerSession' => $customerSession
            ]
        );

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(new Phrase('Incorrect CAPTCHA'));

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/share')
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $model->execute());
    }
}
