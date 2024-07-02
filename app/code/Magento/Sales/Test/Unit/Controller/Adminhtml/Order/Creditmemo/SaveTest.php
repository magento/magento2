<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
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
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
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
     * @var ForwardFactory|MockObject
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
     * @var CreditmemoSender|MockObject
     */
    private $creditmemoSender;

    /**
     * @var SalesData|MockObject
     */
    private $salesData;

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
            ->addMethods(['setFormData'])
            ->setConstructorArgs($constructArguments)
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $registryMock = $this->createMock(Registry::class);
        $this->_objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            Registry::class
        )->willReturn(
            $registryMock
        );
        $this->_messageManager = $this->getMockForAbstractClass(ManagerInterface::class);

        $arguments = [
            'response' => $this->_responseMock,
            'request' => $this->_requestMock,
            'session' => $this->_sessionMock,
            'objectManager' => $this->_objectManager,
            'messageManager' => $this->_messageManager,
            'resultRedirectFactory' => $this->resultRedirectFactoryMock
        ];

        $context = $helper->getObject(Context::class, $arguments);

        $creditmemoManagement =  $this->getMockBuilder(CreditmemoManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_objectManager->expects($this->any())
            ->method('create')
            ->with(CreditmemoManagementInterface::class)
            ->willReturn($creditmemoManagement);
        $this->creditmemoSender = $this->getMockBuilder(CreditMemoSender::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();
        $this->creditmemoSender->expects($this->any())
            ->method('send')
            ->willReturn(true);
        $this->salesData = $this->getMockBuilder(SalesData::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canSendNewCreditmemoEmail'])
            ->getMock();
        $this->memoLoaderMock = $this->createMock(CreditmemoLoader::class);
        $this->_controller = $helper->getObject(
            Save::class,
            [
                'context' => $context,
                'creditmemoLoader' => $this->memoLoaderMock,
                'creditmemoSender' => $this->creditmemoSender,
                'salesData' => $this->salesData
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
            Creditmemo::class,
            ['load', 'getGrandTotal', 'getAllItems']
        );
        $creditmemoMock->expects($this->once())->method('getGrandTotal')->willReturn('1');
        $orderItem = $this->createMock(Order\Item::class);
        $orderItem->expects($this->once())
            ->method('getParentItemId');
        $creditMemoItem = $this->createMock(Item::class);
        $creditMemoItem->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItem);
        $creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$creditMemoItem]);
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
        )->willReturn(
            $data
        );
        $this->_requestMock->expects($this->any())->method('getParam')->willReturn(null);

        $creditmemoMock = $this->createPartialMock(
            Creditmemo::class,
            ['load', 'isValidGrandTotal', 'getAllItems']
        );
        $creditmemoMock->expects($this->once())->method('isValidGrandTotal')->willReturn(false);
        $orderItem = $this->createMock(Order\Item::class);
        $orderItem->expects($this->once())
            ->method('getParentItemId');
        $creditMemoItem = $this->createMock(Item::class);
        $creditMemoItem->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItem);
        $creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$creditMemoItem]);
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
        $this->_messageManager->expects($this->once())->method('addErrorMessage')->with($errorMessage);
        $this->_sessionMock->expects($this->once())->method('setFormData')->with($data);
    }

    /**
     * @return array
     */
    public static function testExecuteEmailsDataProvider()
    {
        /**
        * string $sendEmail
        * bool $emailEnabled
        * bool $shouldEmailBeSent
        */
        return [
            ['', false, false],
            ['', true, false],
            ['on', false, false],
            ['on', true, true]
        ];
    }

    /**
     * @param string $sendEmail
     * @param bool $emailEnabled
     * @param bool $shouldEmailBeSent
     * @dataProvider testExecuteEmailsDataProvider
     */
    public function testExecuteEmails(
        $sendEmail,
        $emailEnabled,
        $shouldEmailBeSent
    ) {
        $orderId = 1;
        $creditmemoId = 2;
        $invoiceId = 3;
        $creditmemoData = ['items' => [], 'send_email' => $sendEmail];

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view', ['order_id' => $orderId])
            ->willReturnSelf();

        $order = $this->createPartialMock(
            Order::class,
            []
        );

        $creditmemo = $this->createPartialMock(
            Creditmemo::class,
            ['isValidGrandTotal', 'getOrder', 'getOrderId', 'getAllItems']
        );
        $creditmemo->expects($this->once())
            ->method('isValidGrandTotal')
            ->willReturn(true);
        $creditmemo->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);
        $creditmemo->expects($this->once())
            ->method('getOrderId')
            ->willReturn($orderId);
        $orderItem = $this->createMock(Order\Item::class);
        $orderItem->expects($this->once())
            ->method('getParentItemId');
        $creditMemoItem = $this->createMock(Item::class);
        $creditMemoItem->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($orderItem);
        $creditmemo->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$creditMemoItem]);

        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['creditmemo_id', null, $creditmemoId],
                    ['creditmemo', null, $creditmemoData],
                    ['invoice_id', null, $invoiceId]
                ]
            );

        $this->_requestMock->expects($this->any())
            ->method('getPost')
            ->willReturn($creditmemoData);

        $this->memoLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($creditmemo);

        $this->salesData->expects($this->any())
            ->method('canSendNewCreditmemoEmail')
            ->willReturn($emailEnabled);
        if ($shouldEmailBeSent) {
            $this->creditmemoSender->expects($this->once())
                ->method('send');
        }
        $this->assertEquals($this->resultRedirectMock, $this->_controller->execute());
    }
}
