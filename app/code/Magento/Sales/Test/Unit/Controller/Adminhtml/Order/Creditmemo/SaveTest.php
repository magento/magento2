<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * Init model for future tests
     */
    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->_responseMock->headersSentThrowsException = false;
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            'Magento\Backend\Model\Session',
            ['storage' => new \Magento\Framework\Session\Storage()]
        );
        $this->_sessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setFormData'],
            $constructArguments
        );
        $this->resultForwardFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\ForwardFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Forward')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
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
            'resultRedirectFactory' => $this->resultRedirectFactoryMock
        ];

        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);

        $this->memoLoaderMock = $this->getMock(
            '\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader', [], [], '', false
        );
        $this->_controller = $helper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save',
            [
                'context' => $context,
                'creditmemoLoader' => $this->memoLoaderMock,
            ]
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
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/new', ['_current' => true])
            ->willReturnSelf();

        $this->_setSaveActionExpectationForMageCoreException(
            $data,
            'Cannot create online refund for Refund to Store Credit.'
        );

        $this->assertInstanceOf(
            'Magento\Backend\Model\View\Result\Redirect',
            $this->_controller->execute()
        );
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
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/*/new', ['_current' => true])
            ->willReturnSelf();

        $this->_setSaveActionExpectationForMageCoreException($data, 'The credit memo\'s total must be positive.');

        $this->_controller->execute();
    }

    /**
     * Set expectations in case of \Magento\Framework\Exception\LocalizedException for saveAction method
     *
     * @param array $data
     * @param string $errorMessage
     */
    protected function _setSaveActionExpectationForMageCoreException($data, $errorMessage)
    {
        $this->_messageManager->expects($this->once())->method('addError')->with($this->equalTo($errorMessage));
        $this->_sessionMock->expects($this->once())->method('setFormData')->with($this->equalTo($data));
    }
}
