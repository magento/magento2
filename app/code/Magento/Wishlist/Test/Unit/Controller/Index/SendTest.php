<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as CustomerData;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Store\Model\Store;
use Magento\Wishlist\Controller\Index\Send;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendTest extends TestCase
{
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
        $this->resultRedirect = $this->getMockBuilder(ResultRedirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultLayout = $this->getMockBuilder(ResultLayout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->willReturnMap([
                [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirect],
                [ResultFactory::TYPE_LAYOUT, [], $this->resultLayout],
            ]);

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->addMethods(['getPost', 'getPostValue'])
            ->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->url = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->eventManager = $this->getMockBuilder(EventManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(ActionContext::class)
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

        $this->formKeyValidator = $this->getMockBuilder(FormKeyValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->addMethods(['getEmail'])
            ->getMock();

        $customerMock->expects($this->any())
            ->method('getEmail')
            ->willReturn('expamle@mail.com');

        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $this->customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomer', 'getData'])
            ->getMock();

        $this->customerSession->expects($this->any())
            ->method('getCustomer')
            ->willReturn($customerMock);

        $this->customerSession->expects($this->any())
            ->method('getData')
            ->willReturn(false);

        $this->wishlistProvider = $this->getMockBuilder(WishlistProviderInterface::class)
            ->getMockForAbstractClass();

        $this->captchaHelper = $this->getMockBuilder(CaptchaHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCaptcha'])
            ->getMock();

        $this->captchaModel = $this->getMockBuilder(CaptchaModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isRequired', 'logAttempt'])
            ->getMock();

        $objectHelper = new ObjectManager($this);

        $this->captchaHelper->expects($this->once())->method('getCaptcha')
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
            ->withConsecutive(['emails'], ['message'])
            ->willReturnOnConsecutiveCalls('some.email2@gmail.com');

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
}
