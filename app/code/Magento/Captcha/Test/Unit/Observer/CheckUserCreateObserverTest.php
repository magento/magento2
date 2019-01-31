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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_actionFlag;

    /*
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlManager;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaStringResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_captcha;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    protected function setUp()
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
        )->will(
            $this->returnValue($redirectUrl)
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
        )->will(
            $this->returnValue($redirectUrl)
        );

        $controller = $this->createMock(\Magento\Framework\App\Action\Action::class);
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $controller->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $this->_captcha->expects($this->any())->method('isRequired')->will($this->returnValue(true));
        $this->_captcha->expects(
            $this->once()
        )->method(
            'isCorrect'
        )->with(
            $captchaValue
        )->will(
            $this->returnValue(false)
        );
        $this->captchaStringResolver->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            $request,
            $formId
        )->will(
            $this->returnValue($captchaValue)
        );
        $this->_helper->expects(
            $this->any()
        )->method(
            'getCaptcha'
        )->with(
            $formId
        )->will(
            $this->returnValue($this->_captcha)
        );
        $this->_messageManager->expects($this->once())->method('addError')->with($warningMessage);
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
