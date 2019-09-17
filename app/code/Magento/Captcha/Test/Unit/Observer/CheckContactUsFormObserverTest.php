<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckContactUsFormObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Captcha\Observer\CheckContactUsFormObserver
     */
    protected $checkContactUsFormObserver;

    /**
     * @var \Magento\Captcha\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlagMock;

    /*
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaStringResolverMock;

    /**
     * @var \Magento\Framework\Session\SessionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Captcha\Model\DefaultModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaMock;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataPersistorMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helperMock = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->actionFlagMock = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->captchaStringResolverMock = $this->createMock(\Magento\Captcha\Observer\CaptchaStringResolver::class);
        $this->sessionMock = $this->createPartialMock(
            \Magento\Framework\Session\SessionManager::class,
            ['addErrorMessage']
        );
        $this->dataPersistorMock = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
            ->getMockForAbstractClass();

        $this->checkContactUsFormObserver = $this->objectManagerHelper->getObject(
            \Magento\Captcha\Observer\CheckContactUsFormObserver::class,
            [
                'helper' => $this->helperMock,
                'actionFlag' => $this->actionFlagMock,
                'messageManager' => $this->messageManagerMock,
                'redirect' => $this->redirectMock,
                'captchaStringResolver' => $this->captchaStringResolverMock
            ]
        );
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->checkContactUsFormObserver,
            'dataPersistor',
            $this->dataPersistorMock
        );

        $this->captchaMock = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);
    }

    public function testCheckContactUsFormWhenCaptchaIsRequiredAndValid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';

        $controller = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->any())
            ->method('getPost')
            ->with(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE, null)
            ->willReturn([$formId => $captchaValue]);
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $this->captchaMock->expects($this->any())->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(true);
        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);
        $this->helperMock->expects($this->any())
            ->method('getCaptcha')
            ->with($formId)->willReturn($this->captchaMock);
        $this->sessionMock->expects($this->never())->method('addErrorMessage');

        $this->checkContactUsFormObserver->execute(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
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

        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $request->expects($this->any())
            ->method('getPost')
            ->with(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE, null)
            ->willReturn([$formId => $captchaValue]);
        $request->expects($this->once())
            ->method('getPostValue')
            ->willReturn($postData);

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($response, $redirectRoutePath, [])
            ->willReturn($redirectUrl);

        $controller = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $controller->expects($this->any())->method('getResponse')->willReturn($response);
        $this->captchaMock->expects($this->any())->method('isRequired')->willReturn(true);
        $this->captchaMock->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->willReturn(false);
        $this->captchaStringResolverMock->expects($this->once())
            ->method('resolve')
            ->with($request, $formId)
            ->willReturn($captchaValue);
        $this->helperMock->expects($this->any())
            ->method('getCaptcha')
            ->with($formId)
            ->willReturn($this->captchaMock);
        $this->messageManagerMock->expects($this->once())->method('addErrorMessage')->with($warningMessage);
        $this->actionFlagMock->expects($this->once())
            ->method('set')
            ->with('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with($formId, $postData);

        $this->checkContactUsFormObserver->execute(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
        );
    }

    public function testCheckContactUsFormDoesNotCheckCaptchaWhenItIsNotRequired()
    {
        $this->helperMock->expects($this->any())
            ->method('getCaptcha')
            ->with('contact_us')
            ->willReturn($this->captchaMock);
        $this->captchaMock->expects($this->any())->method('isRequired')->willReturn(false);
        $this->captchaMock->expects($this->never())->method('isCorrect');

        $this->checkContactUsFormObserver->execute(new \Magento\Framework\Event\Observer());
    }
}
