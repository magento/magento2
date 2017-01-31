<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Observer;

class PreventExpressCheckoutObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\PreventExpressCheckoutObserver
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

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_event = new \Magento\Framework\Event();
        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_customerSession = $this->getMockBuilder(
            'Magento\Customer\Model\Session'
        )->disableOriginalConstructor()->setMethods(
            ['isLoggedIn']
        )->getMock();

        $this->_persistentSession = $this->getMockBuilder(
            'Magento\Persistent\Helper\Session'
        )->disableOriginalConstructor()->setMethods(
            ['isPersistent']
        )->getMock();

        $this->_messageManager = $this->getMockBuilder(
            'Magento\Framework\Message\ManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->_url = $this->getMockBuilder(
            'Magento\Framework\UrlInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->_expressRedirectHelper = $this->getMockBuilder(
            'Magento\Checkout\Helper\ExpressRedirect'
        )->disableOriginalConstructor()->setMethods(
            ['redirectLogin']
        )->getMock();

        $this->_model = $helper->getObject(
            'Magento\Persistent\Observer\PreventExpressCheckoutObserver',
            [
                'customerSession' => $this->_customerSession,
                'persistentSession' => $this->_persistentSession,
                'messageManager' => $this->_messageManager,
                'url' => $this->_url,
                'expressRedirectHelper' => $this->_expressRedirectHelper
            ]
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
        $request = new \Magento\Framework\DataObject();
        $request->setActionName($unexpectedActionName);
        $expressRedirectMock = $this->getMockBuilder(
            'Magento\Checkout\Controller\Express\RedirectLoginInterface'
        )->disableOriginalConstructor()->setMethods(
            [
                'getActionFlagList',
                'getResponse',
                'getCustomerBeforeAuthUrl',
                'getLoginUrl',
                'getRedirectActionName',
                'getRequest',
            ]
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
