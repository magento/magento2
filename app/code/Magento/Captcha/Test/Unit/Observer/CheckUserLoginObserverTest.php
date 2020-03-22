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
use Magento\Captcha\Observer\CheckUserLoginObserver;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserLoginObserverTest extends TestCase
{
    /**
     * @var CheckUserLoginObserver
     */
    private $observer;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    /**
     * @var ActionFlag|MockObject
     */
    private $actionFlagMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var CaptchaStringResolver|MockObject
     */
    private $captchaStringResolverMock;

    /**
     * @var Url|MockObject
     */
    private $customerUrlMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var AuthenticationInterface|MockObject
     */
    private $authenticationMock;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->customerSessionMock = $this->createPartialMock(
            Session::class,
            ['setUsername', 'getBeforeAuthUrl']
        );
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->customerUrlMock = $this->createMock(Url::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->authenticationMock = $this->createMock(AuthenticationInterface::class);

        $objectManager = new ObjectManager($this);
        $this->observer = $objectManager->getObject(
            CheckUserLoginObserver::class,
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

        $captcha = $this->createMock(DefaultModel::class);
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

        $response = $this->createMock(HttpResponse::class);
        $response->expects($this->once())
            ->method('setRedirect')
            ->with($redirectUrl);

        $request = $this->createMock(HttpRequest::class);
        $request->method('getPost')
            ->with('login')
            ->willReturn($loginParams);

        $controller = $this->createMock(Action::class);
        $controller->method('getRequest')->willReturn($request);
        $controller->method('getResponse')->willReturn($response);

        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);

        $customerDataMock = $this->createPartialMock(Customer::class, ['getId']);
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
            ->with('', ActionInterface::FLAG_NO_DISPATCH, true);

        $this->customerSessionMock->expects($this->once())
            ->method('setUsername')
            ->with($login);

        $this->customerSessionMock->expects($this->once())
            ->method('getBeforeAuthUrl')
            ->willReturn(false);

        $this->customerUrlMock->expects($this->once())
            ->method('getLoginUrl')
            ->willReturn($redirectUrl);

        $this->observer->execute(new Observer(['controller_action' => $controller]));
    }
}
