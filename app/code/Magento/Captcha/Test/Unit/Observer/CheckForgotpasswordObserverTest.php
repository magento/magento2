<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Observer;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckForgotpasswordObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Captcha\Observer\CheckForgotpasswordObserver
     */
    protected $checkForgotpasswordObserver;

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
    protected $redirect;

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

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_helper = $this->getMock(\Magento\Captcha\Helper\Data::class, [], [], '', false);
        $this->_actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);
        $this->_messageManager = $this->getMock(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->redirect = $this->getMock(
            \Magento\Framework\App\Response\RedirectInterface::class,
            [],
            [],
            '',
            false
        );
        $this->captchaStringResolver = $this->getMock(
            \Magento\Captcha\Observer\CaptchaStringResolver::class,
            [],
            [],
            '',
            false
        );
        $this->checkForgotpasswordObserver = $this->_objectManager->getObject(
            \Magento\Captcha\Observer\CheckForgotpasswordObserver::class,
            [
                'helper' => $this->_helper,
                'actionFlag' => $this->_actionFlag,
                'messageManager' => $this->_messageManager,
                'redirect' => $this->redirect,
                'captchaStringResolver' => $this->captchaStringResolver
            ]
        );
        $this->_captcha = $this->getMock(\Magento\Captcha\Model\DefaultModel::class, [], [], '', false);
    }

    public function testCheckForgotpasswordRedirects()
    {
        $formId = 'user_forgotpassword';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA';
        $redirectRoutePath = '*/*/forgotpassword';
        $redirectUrl = 'http://magento.com/customer/account/forgotpassword/';

        $request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $request->expects(
            $this->any()
        )->method(
            'getPost'
        )->with(
            \Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE,
            null
        )->will(
            $this->returnValue([$formId => $captchaValue])
        );

        $this->redirect->expects(
            $this->once()
        )->method(
            'redirect'
        )->with(
            $response,
            $redirectRoutePath,
            []
        )->will(
            $this->returnValue($redirectUrl)
        );

        $controller = $this->getMock(\Magento\Framework\App\Action\Action::class, [], [], '', false);
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

        $this->checkForgotpasswordObserver->execute(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
        );
    }
}
