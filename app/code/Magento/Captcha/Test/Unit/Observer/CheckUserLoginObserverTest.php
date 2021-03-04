<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Customer\Model\AuthenticationInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserLoginObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Captcha\Helper\Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $helperMock;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit\Framework\MockObject\MockObject */
    protected $actionFlagMock;

    /* @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerSessionMock;

    /** @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit\Framework\MockObject\MockObject */
    protected $captchaStringResolverMock;

    /** @var \Magento\Customer\Model\Url|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerUrlMock;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerRepositoryMock;

    /** @var AuthenticationInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $authenticationMock;

    /** @var \Magento\Captcha\Observer\CheckUserLoginObserver */
    protected $observer;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->actionFlagMock = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->customerSessionMock = $this->createPartialMock(
            \Magento\Customer\Model\Session::class,
            ['setUsername', 'getBeforeAuthUrl']
        );
        $this->captchaStringResolverMock = $this->createMock(\Magento\Captcha\Observer\CaptchaStringResolver::class);
        $this->customerUrlMock = $this->createMock(\Magento\Customer\Model\Url::class);
        $this->customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->authenticationMock = $this->getMockForAbstractClass(AuthenticationInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observer = $objectManager->getObject(
            \Magento\Captcha\Observer\CheckUserLoginObserver::class,
            [
                'helper' => $this->helperMock,
                'actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->messageManagerMock,
                'customerSession' => $this->customerSessionMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                'customerUrl' => $this->customerUrlMock,
            ]
        );

        $reflection = new \ReflectionClass(get_class($this->observer));
        $reflectionProperty = $reflection->getProperty('authentication');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->observer, $this->authenticationMock);

        $reflectionProperty2 = $reflection->getProperty('customerRepository');
        $reflectionProperty2->setAccessible(true);
        $reflectionProperty2->setValue($this->observer, $this->customerRepositoryMock);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $formId = 'user_login';
        $login = 'login';
        $loginParams = ['username' => $login];
        $customerId = 7;
        $redirectUrl = 'http://magento.com/customer/account/login/';
        $captchaValue = 'some-value';

        $captcha = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);
        $captcha->expects($this->once())
            ->method('isRequired')
            ->with($login)
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);
        $captcha->expects($this->once())
            ->method('logAttempt')
            ->with($login);

        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with($formId)
            ->willReturn($captcha);

        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $response->expects($this->once())
        ->method('setRedirect')
        ->with($redirectUrl);

        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->any())
            ->method('getPost')
            ->with('login')
            ->willReturn($loginParams);

        $controller = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $controller->expects($this->any())->method('getResponse')->willReturn($response);

        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);

        $customerDataMock = $this->createPartialMock(\Magento\Customer\Model\Data\Customer::class, ['getId']);
        $customerDataMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->customerRepositoryMock->expects($this->once())
            ->method('get')
            ->with($login)
            ->willReturn($customerDataMock);

        $this->authenticationMock->expects($this->once())
            ->method('processAuthenticationFailure')
            ->with($customerId);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Incorrect CAPTCHA'));

        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

        $this->customerSessionMock->expects($this->once())
            ->method('setUsername')
            ->with($login);

        $this->customerSessionMock->expects($this->once())
            ->method('getBeforeAuthUrl')
            ->willReturn(false);

        $this->customerUrlMock->expects($this->once())
            ->method('getLoginUrl')
            ->willReturn($redirectUrl);

        $this->observer->execute(new \Magento\Framework\Event\Observer(['controller_action' => $controller]));
    }
}
