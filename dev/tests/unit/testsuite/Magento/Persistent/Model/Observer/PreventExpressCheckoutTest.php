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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Persistent\Model\Observer;

class PreventExpressCheckoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\PreventExpressCheckout
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Event
     */
    protected $_event;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * Customer session
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * Persistent session
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_persistentSession;

    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * Url model
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_expressRedirectHelper;

    public function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_event = new \Magento\Framework\Event();
        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_customerSession = $this->getMockBuilder(
            'Magento\Customer\Model\Session'
        )->disableOriginalConstructor()->setMethods(
            array('isLoggedIn')
        )->getMock();

        $this->_persistentSession = $this->getMockBuilder(
            'Magento\Persistent\Helper\Session'
        )->disableOriginalConstructor()->setMethods(
            array('isPersistent')
        )->getMock();

        $this->_messageManager = $this->getMockBuilder(
            'Magento\Framework\Message\ManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            array()
        )->getMock();

        $this->_url = $this->getMockBuilder(
            'Magento\Framework\UrlInterface'
        )->disableOriginalConstructor()->setMethods(
            array()
        )->getMock();

        $this->_expressRedirectHelper = $this->getMockBuilder(
            'Magento\Checkout\Helper\ExpressRedirect'
        )->disableOriginalConstructor()->setMethods(
            array('redirectLogin')
        )->getMock();

        $this->_model = $helper->getObject(
            'Magento\Persistent\Model\Observer\PreventExpressCheckout',
            array(
                'customerSession' => $this->_customerSession,
                'persistentSession' => $this->_persistentSession,
                'messageManager' => $this->_messageManager,
                'url' => $this->_url,
                'expressRedirectHelper' => $this->_expressRedirectHelper
            )
        );
    }

    public function testPreventExpressCheckoutOnline()
    {
        $this->_customerSession->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->_persistentSession->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->_model->execute($this->_observer);
    }

    public function testPreventExpressCheckoutEmpty()
    {
        $this->_customerSession->expects($this->any())->method('isLoggedIn')->will($this->returnValue(false));
        $this->_persistentSession->expects($this->any())->method('isPersistent')->will($this->returnValue(true));

        $this->_event->setControllerAction(null);
        $this->_model->execute($this->_observer);

        $this->_event->setControllerAction(new \StdClass());
        $this->_model->execute($this->_observer);

        $expectedActionName = 'realAction';
        $unexpectedActionName = 'notAction';
        $request = new \Magento\Framework\Object();
        $request->setActionName($unexpectedActionName);
        $expressRedirectMock = $this->getMockBuilder(
            'Magento\Checkout\Controller\Express\RedirectLoginInterface'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getActionFlagList',
                'getResponse',
                'getCustomerBeforeAuthUrl',
                'getLoginUrl',
                'getRedirectActionName',
                'getRequest'
            )
        )->getMock();
        $expressRedirectMock->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $expressRedirectMock->expects(
            $this->any()
        )->method(
            'getRedirectActionName'
        )->will(
            $this->returnValue($expectedActionName)
        );
        $this->_event->setControllerAction($expressRedirectMock);
        $this->_model->execute($this->_observer);

        $expectedAuthUrl = 'expectedAuthUrl';
        $request->setActionName($expectedActionName);
        $this->_url->expects($this->once())->method('getUrl')->will($this->returnValue($expectedAuthUrl));
        $this->_expressRedirectHelper->expects(
            $this->once()
        )->method(
            'redirectLogin'
        )->with(
            $expressRedirectMock,
            $expectedAuthUrl
        );
        $this->_model->execute($this->_observer);
    }
}
