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
use Magento\Captcha\Observer\CheckContactUsFormObserver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Captcha\Observer\CheckContactUsFormObserver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckContactUsFormObserverTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var CheckContactUsFormObserver
     */
    private $checkContactUsFormObserver;

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
     * @var RedirectInterface|MockObject
     */
    private $redirectMock;

    /**
     * @var CaptchaStringResolver|MockObject
     */
    private $captchaStringResolverMock;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private $dataPersistorMock;

    /**
     * @var SessionManager|MockObject
     */
    private $sessionMock;

    /**
     * @var DefaultModel|MockObject
     */
    private $captchaMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->helperMock = $this->createMock(Data::class);
        $this->actionFlagMock = $this->createMock(ActionFlag::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->captchaStringResolverMock = $this->createMock(CaptchaStringResolver::class);
        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->getMockForAbstractClass();

        $this->sessionMock = $this->getMockBuilder(SessionManager::class)
            ->addMethods(['addErrorMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->captchaMock = $this->createMock(DefaultModel::class);

        $this->checkContactUsFormObserver = $this->objectManagerHelper->getObject(
            CheckContactUsFormObserver::class,
            [
                'helper' => $this->helperMock,
                'actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->messageManagerMock,
                'redirect' => $this->redirectMock,
                'captchaStringResolver' => $this->captchaStringResolverMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    public function testCheckContactUsFormWhenCaptchaIsRequiredAndValid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';

        $controller = $this->createMock(Action::class);
        $request = $this->createMock(Http::class);
        $request->method('getPost')
            ->with(Data::INPUT_NAME_FIELD_VALUE, null)
            ->willReturn([$formId => $captchaValue]);
        $controller->method('getRequest')->willReturn($request);
        $this->captchaMock->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(true);
        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);
        $this->helperMock->method('getCaptcha')
            ->with($formId)
            ->willReturn($this->captchaMock);
        $this->sessionMock->expects($this->never())->method('addErrorMessage');

        $this->checkContactUsFormObserver->execute(
            new Observer(['controller_action' => $controller])
        );
    }

    public function testCheckContactUsFormRedirectsCustomerWithWarningMessageWhenCaptchaIsRequiredAndInvalid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA.';
        $redirectRoutePath = 'contact/index/index';
        $redirectUrl = 'http://magento.com/contacts/';
        $postData = ['name' => 'Some Name'];

        $request = $this->createMock(Http::class);
        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $request->method('getPost')
            ->with(Data::INPUT_NAME_FIELD_VALUE, null)
            ->willReturn([$formId => $captchaValue]);
        $request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($response, $redirectRoutePath, [])
            ->willReturn($redirectUrl);

        $controller = $this->createMock(Action::class);
        $controller->method('getRequest')->willReturn($request);
        $controller->method('getResponse')->willReturn($response);
        $this->captchaMock->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);
        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);
        $this->helperMock->method('getCaptcha')
            ->with($formId)
            ->willReturn($this->captchaMock);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($warningMessage);
        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', Action::FLAG_NO_DISPATCH, true);
        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with($formId, $postData);

        $this->checkContactUsFormObserver->execute(
            new Observer(['controller_action' => $controller])
        );
    }

    public function testCheckContactUsFormDoesNotCheckCaptchaWhenItIsNotRequired()
    {
        $this->helperMock->method('getCaptcha')
            ->with('contact_us')
            ->willReturn($this->captchaMock);
        $this->captchaMock->method('isRequired')->willReturn(false);
        $this->captchaMock->expects($this->never())->method('isCorrect');

        $this->checkContactUsFormObserver->execute(new Observer());
    }
}
