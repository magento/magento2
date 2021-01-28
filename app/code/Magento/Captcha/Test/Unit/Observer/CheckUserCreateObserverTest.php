<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUserCreateObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Captcha\Observer\CheckUserCreateObserver
     */
    protected $checkUserCreateObserver;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_helper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_actionFlag;

    /*
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_messageManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlManager;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $captchaStringResolver;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_captcha;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirect;

    protected function setUp(): void
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->_actionFlag = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->_messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->_session = $this->createMock(\Magento\Framework\Session\SessionManager::class);
        $this->_urlManager = $this->createMock(\Magento\Framework\Url::class);
        $this->captchaStringResolver = $this->createMock(\Magento\Captcha\Observer\CaptchaStringResolver::class);
        $this->redirect = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->checkUserCreateObserver = $this->_objectManager->getObject(
            \Magento\Captcha\Observer\CheckUserCreateObserver::class,
            [
                'helper' => $this->_helper,
                'actionFlag' => $this->_actionFlag,
                'messageManager' => $this->_messageManager,
                'session' => $this->_session,
                'urlManager' => $this->_urlManager,
                'redirect' => $this->redirect,
                'captchaStringResolver' => $this->captchaStringResolver
            ]
        );
        $this->_captcha = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);
    }

    public function testCheckUserCreateRedirectsError()
    {
        $formId = 'user_create';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA';
        $redirectRoutePath = '*/*/create';
        $redirectUrl = 'http://magento.com/customer/account/create/';

        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);

        $this->redirect->expects(
            $this->once()
        )->method(
            'error'
        )->with(
            $redirectUrl
        )->willReturn(
            $redirectUrl
        );

        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $response->expects($this->once())->method('setRedirect')->with($redirectUrl);

        $this->_urlManager->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $redirectRoutePath,
            ['_nosecret' => true]
        )->willReturn(
            $redirectUrl
        );

        $controller = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $controller->expects($this->any())->method('getRequest')->willReturn($request);
        $controller->expects($this->any())->method('getResponse')->willReturn($response);
        $this->_captcha->expects($this->any())->method('isRequired')->willReturn(true);
        $this->_captcha->expects(
            $this->once()
        )->method(
            'isCorrect'
        )->with(
            $captchaValue
        )->willReturn(
            false
        );
        $this->captchaStringResolver->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            $request,
            $formId
        )->willReturn(
            $captchaValue
        );
        $this->_helper->expects(
            $this->any()
        )->method(
            'getCaptcha'
        )->with(
            $formId
        )->willReturn(
            $this->_captcha
        );
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($warningMessage);
        $this->_actionFlag->expects(
            $this->once()
        )->method(
            'set'
        )->with(
            '',
            \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH,
            true
        );

        $this->checkUserCreateObserver->execute(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
        );
    }
}
