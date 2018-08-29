<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Creditmemo;

/**
 * @covers \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction
     */
    protected $printAction;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoLoaderMock;

    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoRepositoryMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoPdfMock;

    /**
     * @var \Zend_Pdf|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdfMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTimeMock;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultForwardMock;

    /**
     * test setup
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();
        $this->creditmemoLoaderMock = $this->getMockBuilder(
            \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader::class
        )->disableOriginalConstructor()
            ->setMethods(
                [
                    'setOrderId',
                    'setCreditmemoId',
                    'setCreditmemo',
                    'setInvoiceId',
                    'load'
                ]
            )
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->creditmemoRepositoryMock = $this->createMock(\Magento\Sales\Api\CreditmemoRepositoryInterface::class);
        $this->creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoPdfMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Pdf\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pdfMock = $this->getMockBuilder(\Zend_Pdf::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->getMock();
        $this->resultForwardFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\ForwardFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultForwardMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->printAction = $objectManager->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction::class,
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
                    [\Magento\Sales\Model\Order\Creditmemo::class, [], $this->creditmemoMock],
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
            ->with(\Magento\Framework\Stdlib\DateTime\DateTime::class)
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
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                'application/pdf'
            )
            ->willReturn($this->responseMock);

        $this->assertInstanceOf(
            \Magento\Framework\App\ResponseInterface::class,
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
            \Magento\Backend\Model\View\Result\Forward::class,
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
