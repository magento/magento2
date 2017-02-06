<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Customer\Model\AuthenticationInterface;
use Zend\Server\Reflection\ReflectionMethod;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserLoginObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Captcha\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperMock;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlagMock;

    /* @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSessionMock;

    /** @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $captchaStringResolverMock;

    /** @var \Magento\Customer\Model\Url|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerUrlMock;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepositoryMock;

    /** @var AuthenticationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authenticationMock;

    /** @var \Magento\Captcha\Observer\CheckUserLoginObserver */
    protected $observer;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->helperMock = $this->getMock(\Magento\Captcha\Helper\Data::class, [], [], '', false);
        $this->actionFlagMock = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);
        $this->messageManagerMock = $this->getMock(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->getMock(
            \Magento\Customer\Model\Session::class,
            ['setUsername', 'getBeforeAuthUrl'],
            [],
            '',
            false
        );
        $this->captchaStringResolverMock = $this->getMock(
            \Magento\Captcha\Observer\CaptchaStringResolver::class,
            [],
            [],
            '',
            false
        );
        $this->customerUrlMock = $this->getMock(
            \Magento\Customer\Model\Url::class,
            [],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        $this->authenticationMock = $this->getMock(
            AuthenticationInterface::class,
            [],
            [],
            '',
            false
        );

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

        $captcha = $this->getMock(\Magento\Captcha\Model\DefaultModel::class, [], [], '', false);
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

        $response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $response->expects($this->once())
        ->method('setRedirect')
        ->with($redirectUrl);

        $request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $request->expects($this->any())
            ->method('getPost')
            ->with('login')
            ->willReturn($loginParams);

        $controller = $this->getMock(\Magento\Framework\App\Action\Action::class, [], [], '', false);
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $controller->expects($this->any())->method('getResponse')->will($this->returnValue($response));

        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);

        $customerDataMock = $this->getMock(
            \Magento\Customer\Model\Data\Customer::class,
            ['getId'],
            [],
            '',
            false
        );
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
            ->method('addError')
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
