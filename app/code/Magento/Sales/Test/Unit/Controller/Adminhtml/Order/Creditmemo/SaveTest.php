<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Storage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo
     */
    protected $_controller;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $_responseMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $_requestMock;

    /**
     * @var Session|MockObject
     */
    protected $_sessionMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $_messageManager;

    /**
     * @var MockObject
     */
    protected $memoLoaderMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * Init model for future tests
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->_responseMock = $this->createMock(Http::class);
        $this->_responseMock->headersSentThrowsException = false;
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $objectManager = new ObjectManager($this);
        $constructArguments = $objectManager->getConstructArguments(
            Session::class,
            ['storage' => new Storage()]
        );
        $this->_sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['setFormData'])
            ->setConstructorArgs($constructArguments)
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager = $this->createMock(ObjectManagerInterface::class);
        $registryMock = $this->createMock(Registry::class);
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            $this->equalTo(Registry::class)
        )->will(
            $this->returnValue($registryMock)
        );
        $this->_messageManager = $this->createMock(ManagerInterface::class);

        $arguments = [
            'response' => $this->_responseMock,
            'request' => $this->_requestMock,
            'session' => $this->_sessionMock,
            'objectManager' => $this->_objectManager,
            'messageManager' => $this->_messageManager,
            'resultRedirectFactory' => $this->resultRedirectFactoryMock
        ];

        $context = $helper->getObject(Context::class, $arguments);

        $this->memoLoaderMock = $this->createMock(CreditmemoLoader::class);
        $this->_controller = $helper->getObject(
            Save::class,
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

        $creditmemoMock = $this->createPartialMock(
            Creditmemo::class,
            ['load', 'getGrandTotal', '__wakeup']
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
            Redirect::class,
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

        $creditmemoMock = $this->createPartialMock(
            Creditmemo::class,
            ['load', 'isValidGrandTotal', '__wakeup']
        );
        $creditmemoMock->expects($this->once())->method('isValidGrandTotal')->will($this->returnValue(false));
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
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($this->equalTo($errorMessage));
        $this->_sessionMock->expects($this->once())->method('setFormData')->with($this->equalTo($data));
    }
}
