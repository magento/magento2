<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends TestCase
{
    /**
     * @var PrintAction
     */
    protected $printAction;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var CreditmemoLoader|MockObject
     */
    protected $creditmemoLoaderMock;

    /**
     * @var CreditmemoRepositoryInterface|MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Creditmemo|MockObject
     */
    protected $creditmemoPdfMock;

    /**
     * @var \Zend_Pdf|MockObject
     */
    protected $pdfMock;

    /**
     * @var DateTime|MockObject
     */
    protected $dateTimeMock;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var Forward|MockObject
     */
    protected $resultForwardMock;

    /**
     * test setup
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->creditmemoLoaderMock = $this->getMockBuilder(
            CreditmemoLoader::class
        )->disableOriginalConstructor()
            ->addMethods(['setOrderId', 'setCreditmemoId', 'setCreditmemo', 'setInvoiceId'])
            ->onlyMethods(['load'])
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->creditmemoRepositoryMock = $this->getMockForAbstractClass(CreditmemoRepositoryInterface::class);
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoPdfMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Pdf\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pdfMock = $this->getMockBuilder(\Zend_Pdf::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            ForwardFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->printAction = $objectManager->getObject(
            PrintAction::class,
            [
                'context' => $this->context,
                'fileFactory' => $this->fileFactoryMock,
                'resultForwardFactory' => $this->resultForwardFactoryMock,
                'creditmemoLoader' => $this->creditmemoLoaderMock,
                'creditmemoRepository' => $this->creditmemoRepositoryMock,
            ]
        );
    }

    /**
     * @covers \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction::execute
     */
    public function testExecute()
    {
        $creditmemoId = 2;
        $date = '2015-01-19_13-03-45';
        $fileName = 'creditmemo2015-01-19_13-03-45.pdf';
        $pdfContent = 'pdf0123456789';
        $fileData = ['type' => 'string', 'value' => $pdfContent, 'rm' => true];
        $this->prepareTestExecute($creditmemoId);

        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [Creditmemo::class, [], $this->creditmemoMock],
                    [\Magento\Sales\Model\Order\Pdf\Creditmemo::class, [], $this->creditmemoPdfMock]
                ]
            );
        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->creditmemoMock);
        $this->creditmemoPdfMock->expects($this->once())
            ->method('getPdf')
            ->with([$this->creditmemoMock])
            ->willReturn($this->pdfMock);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with(DateTime::class)
            ->willReturn($this->dateTimeMock);
        $this->dateTimeMock->expects($this->once())
            ->method('date')
            ->with('Y-m-d_H-i-s')
            ->willReturn($date);
        $this->pdfMock->expects($this->once())
            ->method('render')
            ->willReturn($pdfContent);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $fileName,
                $fileData,
                DirectoryList::VAR_DIR,
                'application/pdf'
            )
            ->willReturn($this->responseMock);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->printAction->execute()
        );
    }

    /**
     * @covers \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction::execute
     */
    public function testExecuteNoCreditmemoId()
    {
        $this->prepareTestExecute();

        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForwardMock);
        $this->resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertInstanceOf(
            Forward::class,
            $this->printAction->execute()
        );
    }

    /**
     * @param int|null $creditmemoId
     */
    protected function prepareTestExecute($creditmemoId = null)
    {
        $orderId = 1;
        $creditmemo = 3;
        $invoiceId = 4;

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['creditmemo_id', null, $creditmemoId],
                    ['creditmemo', null, $creditmemo],
                    ['invoice_id', null, $invoiceId]
                ]
            );
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId)
            ->willReturnSelf();
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemoId')
            ->with($creditmemoId)
            ->willReturnSelf();
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setCreditmemo')
            ->with($creditmemo)
            ->willReturnSelf();
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('setInvoiceId')
            ->with($invoiceId)
            ->willReturnSelf();
        $this->creditmemoLoaderMock->expects($this->once())
            ->method('load');
    }
}
