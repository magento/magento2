<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Captcha\Model\Observer
     */
    protected $_observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_captcha;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_typeOnepage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resLogFactory;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_actionFlag;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_resLogFactory = $this->getMock(
            'Magento\Captcha\Model\Resource\LogFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_resLogFactory->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_getResourceModelStub())
        );

        $this->_session = $this->getMock('Magento\Framework\Session\SessionManager', [], [], '', false);
        $this->_typeOnepage = $this->getMock('Magento\Checkout\Model\Type\Onepage', [], [], '', false);
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->_customerUrl = $this->getMock('Magento\Customer\Model\Url', [], [], '', false);
        $this->_helper = $this->getMock('Magento\Captcha\Helper\Data', [], [], '', false);
        $this->_urlManager = $this->getMock('Magento\Framework\Url', [], [], '', false);
        $this->_actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $this->_messageManager = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );
        $this->redirect = $this->getMock(
            '\Magento\Framework\App\Response\RedirectInterface',
            [],
            [],
            '',
            false
        );
        $this->_observer = $this->_objectManager->getObject(
            'Magento\Captcha\Model\Observer',
            [
                'resLogFactory' => $this->_resLogFactory,
                'session' => $this->_session,
                'typeOnepage' => $this->_typeOnepage,
                'coreData' => $this->_coreData,
                'customerUrl' => $this->_customerUrl,
                'helper' => $this->_helper,
                'urlManager' => $this->_urlManager,
                'actionFlag' => $this->_actionFlag,
                'messageManager' => $this->_messageManager,
                'redirect' => $this->redirect
            ]
        );

        $this->_captcha = $this->getMock('Magento\Captcha\Model\DefaultModel', [], [], '', false);
    }

    public function testCheckContactUsFormWhenCaptchaIsRequiredAndValid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';

        $controller = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
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
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $this->_captcha->expects($this->any())->method('isRequired')->will($this->returnValue(true));
        $this->_captcha->expects(
            $this->once()
        )->method(
            'isCorrect'
        )->with(
            $captchaValue
        )->will(
            $this->returnValue(true)
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
        $this->_session->expects($this->never())->method('addError');

        $this->_observer->checkContactUsForm(
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

        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
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

        $controller = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
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

        $this->_observer->checkContactUsForm(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
        );
    }

    public function testCheckContactUsFormDoesNotCheckCaptchaWhenItIsNotRequired()
    {
        $this->_helper->expects(
            $this->any()
        )->method(
            'getCaptcha'
        )->with(
            'contact_us'
        )->will(
            $this->returnValue($this->_captcha)
        );
        $this->_captcha->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->_captcha->expects($this->never())->method('isCorrect');

        $this->_observer->checkContactUsForm(new \Magento\Framework\Event\Observer());
    }

    public function testCheckForgotpasswordRedirects()
    {
        $formId = 'user_forgotpassword';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA';
        $redirectRoutePath = '*/*/forgotpassword';
        $redirectUrl = 'http://magento.com/customer/account/forgotpassword/';

        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
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

        $controller = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
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

        $this->_observer->checkForgotpassword(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
        );
    }

    public function testCheckUserCreateRedirectsError()
    {
        $formId = 'user_create';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA';
        $redirectRoutePath = '*/*/create';
        $redirectUrl = 'http://magento.com/customer/account/create/';

        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects(
            $this->at(0)
        )->method(
            'getPost'
        )->with(
            \Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE,
            null
        )->will(
            $this->returnValue([$formId => $captchaValue])
        );

        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
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

        $this->redirect->expects(
            $this->once()
        )->method(
            'error'
        )->with(
            $redirectUrl
        )->will(
            $this->returnValue($redirectUrl)
        );

        $controller = $this->getMock('Magento\Framework\App\Action\Action', [], [], '', false);
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

        $this->_observer->checkUserCreate(
            new \Magento\Framework\Event\Observer(['controller_action' => $controller])
        );
    }

    /**
     * Get stub for resource model
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModelStub()
    {
        $resourceModel = $this->getMock(
            'Magento\Captcha\Model\Resource\Log',
            ['deleteUserAttempts', 'deleteOldAttempts', '__wakeup'],
            [],
            '',
            false
        );

        return $resourceModel;
    }
}
