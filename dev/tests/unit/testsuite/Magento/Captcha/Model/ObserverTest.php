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
 * @package     Magento_Captcha
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_resLogFactory = $this->getMock('Magento\Captcha\Model\Resource\LogFactory',
            array('create'), array(), '', false);
        $this->_resLogFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_getResourceModelStub()));

        $this->_session = $this->getMock('Magento\Core\Model\Session\AbstractSession', array(), array(), '', false);
        $this->_typeOnepage = $this->getMock('Magento\Checkout\Model\Type\Onepage', array(), array(), '', false);
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $this->_customerData = $this->getMock('Magento\Customer\Helper\Data', array(), array(), '', false);
        $this->_helper = $this->getMock('Magento\Captcha\Helper\Data', array(), array(), '', false);
        $this->_urlManager = $this->getMock('Magento\Core\Model\Url', array(), array(), '', false);
        $this->_filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        
        $this->_observer = $this->_objectManager->getObject(
            'Magento\Captcha\Model\Observer',
            array(
                'resLogFactory' => $this->_resLogFactory,
                'session' => $this->_session,
                'typeOnepage' => $this->_typeOnepage,
                'coreData' => $this->_coreData,
                'customerData' => $this->_customerData,
                'helper' => $this->_helper,
                'urlManager' => $this->_urlManager,
                'filesystem' => $this->_filesystem,
            )
        );

        $this->_captcha = $this->getMock('Magento\Captcha\Model\DefaultModel', array(), array(), '', false);
    }

    public function testCheckContactUsFormWhenCaptchaIsRequiredAndValid()
    {
        $formId = 'contact_us';
        $captchaValue = 'some-value';

        $controller = $this->getMock('Magento\Core\Controller\Varien\Action', array(), array(), '', false);
        $request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $request->expects($this->any())
            ->method('getPost')
            ->with(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE, null)
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
        $this->_helper->expects($this->any())
            ->method('getCaptcha')
            ->with($formId)
            ->will($this->returnValue($this->_captcha));
        $this->_session->expects($this->never())->method('addError');

        $this->_observer->checkContactUsForm(new \Magento\Event\Observer(array('controller_action' => $controller)));
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

        $controller = $this->getMock('Magento\Core\Controller\Varien\Action', array(), array(), '', false);
        $request = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $response = $this->getMock('Magento\App\Response\Http', array(), array(), '', false);
        $request->expects($this->any())->method('getPost')->with(\Magento\Captcha\Helper\Data::INPUT_NAME_FIELD_VALUE,
            null)
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
        $this->_helper->expects($this->any())->method('getCaptcha')
            ->with($formId)
            ->will($this->returnValue($this->_captcha));
        $this->_session->expects($this->once())->method('addError')->with($warningMessage);
        $controller->expects($this->once())->method('setFlag')
            ->with('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);

        $this->_observer->checkContactUsForm(new \Magento\Event\Observer(array('controller_action' => $controller)));
    }

    public function testCheckContactUsFormDoesNotCheckCaptchaWhenItIsNotRequired()
    {
        $this->_helper->expects($this->any())->method('getCaptcha')
            ->with('contact_us')
            ->will($this->returnValue($this->_captcha));
        $this->_captcha->expects($this->any())->method('isRequired')->will($this->returnValue(false));
        $this->_captcha->expects($this->never())->method('isCorrect');

        $this->_observer->checkContactUsForm(new \Magento\Event\Observer());
    }

    /**
     * Get stub for resource model
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModelStub()
    {
        $resourceModel = $this->getMock('Magento\Captcha\Model\Resource\Log',
            array('deleteUserAttempts', 'deleteOldAttempts'), array(), '', false);

        return $resourceModel;
    }
}
