<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManager\ObjectManager as FrameworkObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Controller\Adminhtml\Order\Invoice\Save;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $resultPageFactoryMock;

    /**
     * @var MockObject
     */
    protected $formKeyValidatorMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $messageManagerMock;

    /**
     * @var Save
     */
    protected $controller;

    /**
     * @var SalesData|MockObject
     */
    private $salesData;

    /**
     * @var InvoiceSender|MockObject
     */
    private $invoiceSender;

    /**
     * @var FrameworkObjectManager|MockObject
     */
    private $objectManager;

    /**
     * @var InvoiceService|MockObject
     */
    private $invoiceService;

    /**
     * SetUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->formKeyValidatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultPageFactoryMock);
        $contextMock->expects($this->any())
            ->method('getFormKeyValidator')
            ->willReturn($this->formKeyValidatorMock);
        $contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->objectManager = $this->createPartialMock(
            FrameworkObjectManager::class,
            ['create','get']
        );
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->invoiceSender = $this->getMockBuilder(InvoiceSender::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();
        $this->invoiceSender->expects($this->any())
            ->method('send')
            ->willReturn(true);
        $this->salesData = $this->getMockBuilder(SalesData::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canSendNewInvoiceEmail'])
            ->getMock();
        $this->invoiceService = $this->getMockBuilder(InvoiceService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepareInvoice'])
            ->getMock();

        $this->controller = $objectManager->getObject(
            Save::class,
            [
                'context' => $contextMock,
                'invoiceSender' => $this->invoiceSender,
                'invoiceService' => $this->invoiceService,
                'salesData' => $this->salesData
            ]
        );
    }

    /**
     * Test execute
     *
     * @return void
     */
    public function testExecuteNotValidPost(): void
    {
        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(false);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with("The invoice can't be saved at this time. Please try again later.");
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order/index')
            ->willReturnSelf();

        $this->assertEquals($redirectMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public static function testExecuteEmailsDataProvider(): array
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
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider testExecuteEmailsDataProvider
     */
    public function testExecuteEmails(
        string $sendEmail,
        bool $emailEnabled,
        bool $shouldEmailBeSent
    ): void {
        $redirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view')
            ->willReturnSelf();

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);
        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $invoiceData = ['items' => [], 'send_email' => $sendEmail];

        $orderId = 2;
        $order = $this->createPartialMock(
            Order::class,
            ['load','getId','canInvoice']
        );
        $order->expects($this->once())
            ->method('load')
            ->willReturn($order);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $order->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTotalQty', 'getOrder', 'register'])
            ->getMock();
        $invoice->expects($this->any())
            ->method('getTotalQty')
            ->willReturn(1);
        $invoice->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);
        $invoice->expects($this->once())
            ->method('register')
            ->willReturn($order);

        $this->invoiceService->expects($this->any())
            ->method('prepareInvoice')
            ->willReturn($invoice);

        $saveTransaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addObject', 'save'])
            ->getMock();
        $saveTransaction
            ->method('addObject')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [$invoice] => $saveTransaction,
                [$order] => $saveTransaction
            });

        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCommentText'])
            ->getMock();
        $session->expects($this->once())
            ->method('getCommentText')
            ->with(true);

        $this->objectManager->expects($this->any())
            ->method('create')
            ->will(
                $this->returnValueMap(
                    [
                        [Transaction::class, [], $saveTransaction],
                        [Order::class, [], $order],
                        [Session::class, [], $session]
                    ]
                )
            );
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(Session::class)
            ->willReturn($session);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['invoice', null, $invoiceData]
                ]
            );
        $this->requestMock->expects($this->any())
            ->method('getPost')
            ->willReturn($invoiceData);

        $this->salesData->expects($this->any())
            ->method('canSendNewInvoiceEmail')
            ->willReturn($emailEnabled);
        if ($shouldEmailBeSent) {
            $this->invoiceSender->expects($this->once())
                ->method('send');
        }

        $this->assertEquals($redirectMock, $this->controller->execute());
    }
}
