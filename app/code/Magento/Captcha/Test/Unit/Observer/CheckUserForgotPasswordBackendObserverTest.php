<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Captcha\Test\Unit\Observer;

use Magento\Captcha\Helper\Data as DataHelper;
use Magento\Captcha\Model\CaptchaInterface;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Captcha\Observer\CheckUserForgotPasswordBackendObserver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for \Magento\Captcha\Observer\CheckUserForgotPasswordBackendObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserForgotPasswordBackendObserverTest extends TestCase
{
    /**
     * @var MockObject|DataHelper
     */
    private $helperMock;

    /**
     * @var MockObject|CaptchaStringResolver
     */
    private $captchaStringResolverMock;

    /**
     * @var MockObject|SessionManagerInterface
     */
    private $sessionMock;

    /**
     * @var MockObject|ActionFlag
     */
    private $actionFlagMock;

    /**
     * @var MockObject|ManagerInterface
     */
    private $messageManagerMock;

    /**
     * @var CheckUserForgotPasswordBackendObserver
     */
    private $observer;

    /**
     * @var MockObject
     */
    private $captchaMock;

    /**
     * @var MockObject|Observer
     */
    private $eventObserverMock;

    /**
     * @var MockObject|Action
     */
    private $controllerMock;

    /**
     * @var MockObject|HttpResponse
     */
    private $httpResponseMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $formId = 'backend_forgotpassword';
        $email = 'stub@test.mail';
        $requestParams = ['STUB_PARAM'];

        $this->helperMock = $this->createMock(DataHelper::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->sessionMock = $this->getMockBuilder(SessionManagerInterface::class)
            ->setMethods(['setEmail'])
            ->getMockForAbstractClass();
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->observer = $objectManager->getObject(
            CheckUserForgotPasswordBackendObserver::class,
            [
                '_helper' => $this->helperMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                '_session' => $this->sessionMock,
                '_actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->messageManagerMock
            ]
        );

        $this->captchaMock = $this->getMockBuilder(CaptchaInterface::class)
            ->setMethods(['isRequired', 'isCorrect'])
            ->getMockForAbstractClass();
        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with($formId)
            ->willReturn($this->captchaMock);

        $requestMock = $this->createMock(HttpRequest::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->with('email')
            ->willReturn($email);
        $requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($requestParams);
        $this->httpResponseMock = $this->createMock(HttpResponse::class);

        $this->controllerMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl', 'getRequest', 'getResponse'])
            ->getMockForAbstractClass();
        $this->controllerMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($requestMock);
        $this->controllerMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->httpResponseMock);

        $this->eventObserverMock = $this->createPartialMock(Observer::class, ['getControllerAction']);
        $this->eventObserverMock->expects($this->any())
            ->method('getControllerAction')
            ->willReturn($this->controllerMock);
    }

    /**
     * Test case when Captcha is required and was entered correctly.
     */
    public function testExecuteWhenCaptchaIsCorrect()
    {
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())->method('isCorrect')->willReturn(true);
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');
        $this->httpResponseMock->expects($this->never())->method('setRedirect');

        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * Test case when Captcha is required and was entered incorrectly.
     */
    public function testExecuteWhenCaptchaIsIncorrect()
    {
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())->method('isCorrect')->willReturn(false);

        $this->sessionMock->expects($this->once())->method('setEmail');
        $this->actionFlagMock->expects($this->once())->method('set');
        $this->controllerMock->expects($this->once())->method('getUrl');
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Incorrect CAPTCHA'));
        $this->httpResponseMock->expects($this->once())->method('setRedirect')->willReturnSelf();

        $this->observer->execute($this->eventObserverMock);
    }

    /**
     * Test case when Captcha is not required.
     */
    public function testExecuteWhenCaptchaIsNotRequired()
    {
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn(false);
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');
        $this->httpResponseMock->expects($this->never())->method('setRedirect');

        $this->observer->execute($this->eventObserverMock);
    }
}
