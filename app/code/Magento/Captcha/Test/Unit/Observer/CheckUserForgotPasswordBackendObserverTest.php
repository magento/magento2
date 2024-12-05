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
    private const STUB_EMAIL = 'stub@test.mail';
    private const STUB_REQUEST_PARAMS = ['STUB_PARAM'];

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
     * @var MockObject|CaptchaInterface
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
     * @var MockObject|HttpRequest
     */
    private $requestMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $formId = 'backend_forgotpassword';

        $this->helperMock = $this->createMock(DataHelper::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->sessionMock = $this->getMockBuilder(SessionManagerInterface::class)
            ->addMethods(['setEmail'])
            ->getMockForAbstractClass();
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->requestMock = $this->createMock(HttpRequest::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->observer = $objectManager->getObject(
            CheckUserForgotPasswordBackendObserver::class,
            [
                '_helper' => $this->helperMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                '_session' => $this->sessionMock,
                '_actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->messageManagerMock,
                'request' => $this->requestMock
            ]
        );

        $this->captchaMock = $this->getMockBuilder(CaptchaInterface::class)
            ->addMethods(['isRequired'])
            ->onlyMethods(['isCorrect'])
            ->getMockForAbstractClass();
        $this->helperMock->expects($this->once())
            ->method('getCaptcha')
            ->with($formId)
            ->willReturn($this->captchaMock);

        $this->httpResponseMock = $this->createMock(HttpResponse::class);

        $this->controllerMock = $this->getMockBuilder(Action::class)
            ->disableOriginalConstructor()
            ->addMethods(['getUrl'])
            ->onlyMethods(['getResponse'])
            ->getMockForAbstractClass();
        $this->controllerMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->httpResponseMock);

        $this->eventObserverMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getControllerAction'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverMock->expects($this->any())
            ->method('getControllerAction')
            ->willReturn($this->controllerMock);
    }

    /**
     * Test case when Captcha is required and was entered correctly.
     */
    public function testExecuteWhenCaptchaIsCorrect()
    {
        $this->configureRequestMockWithStubValues();
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())->method('isCorrect')->willReturn(true);

        $this->executeOriginalMethodExpectsNoError();
    }

    /**
     * Test case when Captcha is required and was entered incorrectly.
     */
    public function testExecuteWhenCaptchaIsIncorrect()
    {
        $this->configureRequestMockWithStubValues();
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
        $this->configureRequestMockWithStubValues();
        $this->captchaMock->expects($this->once())->method('isRequired')->willReturn(false);

        $this->executeOriginalMethodExpectsNoError();
    }

    /**
     * Test case when email is not provided
     */
    public function testExecuteWhenEmailParamIsNotPresent()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('email')
            ->willReturn(null);
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn(self::STUB_REQUEST_PARAMS);
        $this->captchaMock->expects($this->never())->method('isRequired');
        $this->captchaMock->expects($this->never())->method('isCorrect');

        $this->executeOriginalMethodExpectsNoError();
    }

    /**
     * Stub params for Request Mock
     */
    private function configureRequestMockWithStubValues()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('email')
            ->willReturn(self::STUB_EMAIL);
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn(self::STUB_REQUEST_PARAMS);
    }

    /**
     * Run original method, expect there is no error
     */
    private function executeOriginalMethodExpectsNoError()
    {
        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');
        $this->httpResponseMock->expects($this->never())->method('setRedirect');

        $this->observer->execute($this->eventObserverMock);
    }
}
