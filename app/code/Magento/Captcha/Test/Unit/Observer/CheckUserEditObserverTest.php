<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Customer\Model\AuthenticationInterface;

class CheckUserEditObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Captcha\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperMock;

    /** @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject */
    protected $actionFlagMock;

    /* @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirectMock;

    /** @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $captchaStringResolverMock;

    /** @var AuthenticationInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $authenticationMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSessionMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    /** @var \Magento\Captcha\Observer\CheckUserEditObserver */
    protected $observer;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->helperMock = $this->getMock('\Magento\Captcha\Helper\Data', [], [], '', false);
        $this->actionFlagMock = $this->getMock('\Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->messageManagerMock = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->redirectMock = $this->getMock(
            '\Magento\Framework\App\Response\RedirectInterface',
            [],
            [],
            '',
            false
        );
        $this->captchaStringResolverMock = $this->getMock(
            '\Magento\Captcha\Observer\CaptchaStringResolver',
            [],
            [],
            '',
            false
        );
        $this->authenticationMock = $this->getMockBuilder(AuthenticationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMock(
            '\Magento\Customer\Model\Session',
            ['getCustomerId', 'getCustomer', 'logout', 'start'],
            [],
            '',
            false
        );
        $this->scopeConfigMock = $this->getMock(
            '\Magento\Framework\App\Config\ScopeConfigInterface',
            [],
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observer = $objectManager->getObject(
            'Magento\Captcha\Observer\CheckUserEditObserver',
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

        $captcha = $this->getMock('Magento\Captcha\Model\DefaultModel', [], [], '', false);
        $captcha->expects($this->once())
            ->method('isRequired')
            ->willReturn(true);
        $captcha->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);

        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with(\Magento\Captcha\Observer\CheckUserEditObserver::FORM_ID)
            ->willReturn($captcha);

        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->any())
            ->method('getPost')
            ->with(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE, null)
            ->willReturn([\Magento\Captcha\Observer\CheckUserEditObserver::FORM_ID => $captchaValue]);

        $controller = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $controller->expects($this->any())->method('getResponse')->will($this->returnValue($response));

        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, \Magento\Captcha\Observer\CheckUserEditObserver::FORM_ID)
            ->willReturn($captchaValue);

        $customerDataMock = $this->getMock(
            '\Magento\Customer\Model\Data\Customer',
            [],
            [],
            '',
            false
        );

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
            ->method('addError')
            ->withConsecutive([$message], [__('Incorrect CAPTCHA')]);

        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($response, '*/*/edit')
            ->willReturn($redirectUrl);

        $this->observer->execute(new \Magento\Framework\Event\Observer(['controller_action' => $controller]));
    }
}
