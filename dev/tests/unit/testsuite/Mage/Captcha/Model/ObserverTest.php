<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Mage_Captcha
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Captcha_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Captcha_Model_Observer
     */
    protected $_observer;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_captcha;

    protected function setUp()
    {
        $this->_customerSession = $this->getMock('Mage_Customer_Model_Session', array(), array(), '', false);
        $this->_helper = $this->getMock('Mage_Captcha_Helper_Data', array(), array(), '', false);
        $this->_urlManager = $this->getMock('Mage_Core_Model_Url', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento_Filesystem', array(), array(), '', false);
        $this->_observer = new Mage_Captcha_Model_Observer(
            $this->_customerSession,
            $this->_helper,
            $this->_urlManager,
            $this->_filesystem
        );
        $this->_captcha = $this->getMock('Mage_Captcha_Model_Default', array(), array(), '', false);
    }

    public function testCheckContactUsFormWhenCaptchaIsRequiredAndValid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';

        $controller = $this->getMock('Mage_Core_Controller_Varien_Action', array(), array(), '', false);
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $request->expects($this->any())
            ->method('getPost')
            ->with(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE, null)
            ->will($this->returnValue(array(
                $formId => $captchaValue,
            )));
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $this->_captcha->expects($this->any())
            ->method('isRequired')
            ->will($this->returnValue(true));
        $this->_captcha->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->will($this->returnValue(true));
        $this->_helper->expects($this->never())->method('__');
        $this->_helper->expects($this->any())
            ->method('getCaptcha')
            ->with($formId)
            ->will($this->returnValue($this->_captcha));
        $this->_customerSession->expects($this->never())->method('addError');

        $this->_observer->checkContactUsForm(new Varien_Event_Observer(array('controller_action' => $controller)));
    }

    public function testCheckContactUsFormRedirectsCustomerWithWarningMessageWhenCaptchaIsRequiredAndInvalid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';
        $warningMessage = 'Incorrect CAPTCHA.';
        $redirectRoutePath = 'contacts/index/index';
        $redirectUrl = 'http://magento.com/contacts/';

        $this->_urlManager->expects($this->once())
            ->method('getUrl')
            ->with($redirectRoutePath, null)
            ->will($this->returnValue($redirectUrl));

        $controller = $this->getMock('Mage_Core_Controller_Varien_Action', array(), array(), '', false);
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $response = $this->getMock('Mage_Core_Controller_Response_Http', array(), array(), '', false);
        $request->expects($this->any())->method('getPost')->with(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE, null)
            ->will($this->returnValue(array(
                $formId => $captchaValue,
            )));
        $response->expects($this->once())
            ->method('setRedirect')
            ->with($redirectUrl, 302);
        $controller->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $controller->expects($this->any())->method('getResponse')->will($this->returnValue($response));
        $this->_captcha->expects($this->any())->method('isRequired')->will($this->returnValue(true));
        $this->_captcha->expects($this->once())
            ->method('isCorrect')
            ->with($captchaValue)
            ->will($this->returnValue(false));
        $this->_helper->expects($this->any())
            ->method('__')
            ->with($warningMessage)
            ->will($this->returnValue($warningMessage));
        $this->_helper->expects($this->any())->method('getCaptcha')
            ->with($formId)
            ->will($this->returnValue($this->_captcha));
        $this->_customerSession->expects($this->once())->method('addError')->with($warningMessage);
        $controller->expects($this->once())->method('setFlag')
            ->with('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);

        $this->_observer->checkContactUsForm(new Varien_Event_Observer(array('controller_action' => $controller)));
    }

    public function testCheckContactUsFormDoesNotCheckCaptchaWhenItIsNotRequired()
    {
        $this->_helper->expects($this->any())->method('getCaptcha')
            ->with('contact_us')
            ->will($this->returnValue($this->_captcha));
        $this->_captcha->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->_captcha->expects($this->never())->method('isCorrect');

        $this->_observer->checkContactUsForm(new Varien_Event_Observer());
    }
}
