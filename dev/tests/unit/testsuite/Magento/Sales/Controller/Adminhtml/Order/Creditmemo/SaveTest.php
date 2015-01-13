<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Creditmemo;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo
     */
    protected $_controller;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $memoLoaderMock;

    /**
     * Init model for future tests
     */
    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->_responseMock->headersSentThrowsException = false;
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            'Magento\Backend\Model\Session',
            ['storage' => new \Magento\Framework\Session\Storage()]
        );
        $this->_sessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setFormData'],
            $constructArguments
        );
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $registryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false, false);
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $this->equalTo('Magento\Framework\Registry')
        )->will(
            $this->returnValue($registryMock)
        );
        $this->_messageManager = $this->getMock(
            '\Magento\Framework\Message\ManagerInterface',
            [],
            [],
            '',
            false
        );

        $arguments = [
            'response' => $this->_responseMock,
            'request' => $this->_requestMock,
            'session' => $this->_sessionMock,
            'objectManager' => $this->_objectManager,
            'messageManager' => $this->_messageManager,
        ];

        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);

        $this->memoLoaderMock = $this->getMock(
            '\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader', [], [], '', false
        );
        $this->_controller = $helper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save',
            ['context' => $context, 'creditmemoLoader' => $this->memoLoaderMock]
        );
    }

    /**
     * Test saveAction when was chosen online refund with refund to store credit
     */
    public function testSaveActionOnlineRefundToStoreCredit()
    {
        $data = ['comment_text' => '', 'do_offline' => '0', 'refund_customerbalance_return_enable' => '1'];
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'creditmemo'
        )->will(
            $this->returnValue($data)
        );
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(null));

        $creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            ['load', 'getGrandTotal', '__wakeup'],
            [],
            '',
            false
        );
        $creditmemoMock->expects($this->once())->method('getGrandTotal')->will($this->returnValue('1'));
        $this->memoLoaderMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->returnValue($creditmemoMock)
        );

        $this->_setSaveActionExpectationForMageCoreException(
            $data,
            'Cannot create online refund for Refund to Store Credit.'
        );

        $this->_controller->execute();
    }

    /**
     * Test saveAction when was credit memo total is not positive
     */
    public function testSaveActionWithNegativeCreditmemo()
    {
        $data = ['comment_text' => ''];
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'creditmemo'
        )->will(
            $this->returnValue($data)
        );
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(null));

        $creditmemoMock = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            ['load', 'getGrandTotal', 'getAllowZeroGrandTotal', '__wakeup'],
            [],
            '',
            false
        );
        $creditmemoMock->expects($this->once())->method('getGrandTotal')->will($this->returnValue('0'));
        $creditmemoMock->expects($this->once())->method('getAllowZeroGrandTotal')->will($this->returnValue(false));
        $this->memoLoaderMock->expects(
            $this->once()
        )->method(
            'load'
        )->will(
            $this->returnValue($creditmemoMock)
        );

        $this->_setSaveActionExpectationForMageCoreException($data, 'Credit memo\'s total must be positive.');

        $this->_controller->execute();
    }

    /**
     * Set expectations in case of \Magento\Framework\Model\Exception for saveAction method
     *
     * @param array $data
     * @param string $errorMessage
     */
    protected function _setSaveActionExpectationForMageCoreException($data, $errorMessage)
    {
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($errorMessage));
        $this->_sessionMock->expects($this->once())->method('setFormData')->with($this->equalTo($data));

        $this->_responseMock->expects($this->once())->method('setRedirect');
    }
}
