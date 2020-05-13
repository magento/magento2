<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\DefaultModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Captcha\Observer\CheckUserEditObserver;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserEditObserverTest extends TestCase
{
    /** @var Data|MockObject */
    protected $helperMock;

    /** @var ActionFlag|MockObject */
    protected $actionFlagMock;

    /* @var \Magento\Framework\Message\ManagerInterface|MockObject */
    protected $messageManagerMock;

    /** @var RedirectInterface|MockObject */
    protected $redirectMock;

    /** @var CaptchaStringResolver|MockObject */
    protected $captchaStringResolverMock;

    /** @var AuthenticationInterface|MockObject */
    protected $authenticationMock;

    /** @var Session|MockObject */
    protected $customerSessionMock;

    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfigMock;

    /** @var CheckUserEditObserver */
    protected $observer;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->customerSessionMock = $this->createPartialMock(
            Session::class,
            ['getCustomerId', 'getCustomer', 'logout', 'start']
        );
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->observer = $objectManager->getObject(
            CheckUserEditObserver::class,
            [
                'helper' => $this->helperMock,
                'actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->messageManagerMock,
                'redirect' => $this->redirectMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                'authentication' => $this->authenticationMock,
                'customerSession' => $this->customerSessionMock,
                'scopeConfig' => $this->scopeConfigMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $customerId = 7;
        $captchaValue = 'some-value';
        $email = 'test@example.com';
        $redirectUrl = 'http://magento.com/customer/account/edit/';

        $captcha = $this->createMock(DefaultModel::class);
        $captcha->expects($this->once())
            ->method('isRequired')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);

        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(CheckUserEditObserver::FORM_ID)
            ->willReturn($captcha);

        $response = $this->createMock(Http::class);
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->any())
            ->method('getPost')
            ->with(Data::INPUT_NAME_FIELD_VALUE, null)
            ->willReturn([CheckUserEditObserver::FORM_ID => $captchaValue]);

        $controller = $this->createMock(Action::class);
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $controller->expects($this->any())->method('getResponse')->willReturn($response);

        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, CheckUserEditObserver::FORM_ID)
            ->willReturn($captchaValue);

        $customerDataMock = $this->createMock(Customer::class);

        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->customerSessionMock->expects($this->atLeastOnce())
            ->method('getCustomer')
            ->willReturn($customerDataMock);

        $this->authenticationMock->expects($this->once())
            ->method('processAuthenticationFailure')
            ->with($customerId);
        $this->authenticationMock->expects($this->once())
            ->method('isLocked')
            ->with($customerId)
            ->willReturn(true);

        $this->customerSessionMock->expects($this->once())
            ->method('logout');
        $this->customerSessionMock->expects($this->once())
            ->method('start');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('contact/email/recipient_email')
            ->willReturn($email);

        $message = __('The account is locked. Please wait and try again or contact %1.', $email);
        $this->messageManagerMock->expects($this->exactly(2))
            ->method('addErrorMessage')
            ->withConsecutive([$message], [__('Incorrect CAPTCHA')]);

        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', Action::FLAG_NO_DISPATCH, true);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($response, '*/*/edit')
            ->willReturn($redirectUrl);

        $this->observer->execute(new Observer(['controller_action' => $controller]));
    }
}
