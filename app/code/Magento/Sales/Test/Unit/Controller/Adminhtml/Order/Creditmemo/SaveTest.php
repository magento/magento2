<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo
     */
    protected $_controller;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_sessionMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_messageManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $memoLoaderMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectMock;

    /**
     * Init model for future tests
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->_responseMock->headersSentThrowsException = false;
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            \Magento\Backend\Model\Session::class,
            ['storage' => new \Magento\Framework\Session\Storage()]
        );
        $this->_sessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->setMethods(['setFormData'])
            ->setConstructorArgs($constructArguments)
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $this->equalTo(\Magento\Framework\Registry::class)
        )->willReturn(
            $registryMock
        );
        $this->_messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $arguments = [
            'response' => $this->_responseMock,
            'request' => $this->_requestMock,
            'session' => $this->_sessionMock,
            'objectManager' => $this->_objectManager,
            'messageManager' => $this->_messageManager,
            'resultRedirectFactory' => $this->resultRedirectFactoryMock
        ];

        $context = $helper->getObject(\Magento\Backend\App\Action\Context::class, $arguments);

        $this->memoLoaderMock = $this->createMock(\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader::class);
        $this->_controller = $helper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save::class,
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
        )->willReturn(
            $data
        );
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(null);

        $creditmemoMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Creditmemo::class,
            ['load', 'getGrandTotal', '__wakeup']
        );
        $creditmemoMock->expects($this->once())->method('getGrandTotal')->willReturn('1');
        $this->memoLoaderMock->expects(
            $this->once()
        )->method(
            'load'
        )->willReturn(
            $creditmemoMock
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
            \Magento\Backend\Model\View\Result\Redirect::class,
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
        )->willReturn(
            $data
        );
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(null);

        $creditmemoMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Creditmemo::class,
            ['load', 'isValidGrandTotal', '__wakeup']
        );
        $creditmemoMock->expects($this->once())->method('isValidGrandTotal')->willReturn(false);
        $this->memoLoaderMock->expects(
            $this->once()
        )->method(
            'load'
        )->willReturn(
            $creditmemoMock
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
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($this->equalTo($errorMessage));
        $this->_sessionMock->expects($this->once())->method('setFormData')->with($this->equalTo($data));
    }
}
