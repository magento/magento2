<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Import;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Tests the import process
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\ImportExport\Model\Report\ReportProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportProcessorMock;

    /**
     * @var \Magento\ImportExport\Model\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyMock;

    /**
     * @var \Magento\ImportExport\Helper\Report|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reportHelperMock;

    /**
     * @var \Magento\ImportExport\Controller\Adminhtml\Import\Start
     */
    private $start;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var \Magento\ImportExport\Model\Import|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importModelMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getPostValue',
                'isPost',
            ])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);

        $this->reportProcessorMock = $this->getMockBuilder(
            \Magento\ImportExport\Model\Report\ReportProcessorInterface::class
        )
            ->getMockForAbstractClass();

        $this->historyMock = $this->getMockBuilder(\Magento\ImportExport\Model\History::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportHelperMock = $this->getMockBuilder(\Magento\ImportExport\Helper\Report::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->importModelMock = $this->getMockBuilder(\Magento\ImportExport\Model\Import::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->start = new \Magento\ImportExport\Controller\Adminhtml\Import\Start(
            $this->contextMock,
            $this->reportProcessorMock,
            $this->historyMock,
            $this->reportHelperMock,
            $this->importModelMock
        );
    }

    /**
     * Test execute() method
     *
     * Check the case in which import was successful
     */
    public function testImportSuccess()
    {
        $data = ['post' => 1];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $resultBlockMock = $this->getMockBuilder(\Magento\ImportExport\Block\Adminhtml\Import\Frame\Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultBlockMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $resultBlockMock->expects($this->exactly(3))
            ->method('addAction')
            ->willReturnSelf();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlockMock);

        $resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $processingErrorAggregatorMock = $this->getMockBuilder(ProcessingErrorAggregatorInterface::class)
            ->getMockForAbstractClass();

        $this->importModelMock->expects($this->any())
            ->method('getErrorAggregator')
            ->willReturn($processingErrorAggregatorMock);

        $resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->never())
            ->method('setPath');

        $this->resultFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturnMap([
                [\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT, [], $resultLayoutMock],
            ]);

        $this->assertEquals($resultLayoutMock, $this->start->execute());
    }

    /**
     * Test execute() method
     *
     * Check the case in which import resulted in a system error
     */
    public function testImportError()
    {
        $data = ['post' => 1];

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $resultBlockMock = $this->getMockBuilder(\Magento\ImportExport\Block\Adminhtml\Import\Frame\Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultBlockMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $resultBlockMock->expects($this->exactly(3))
            ->method('addAction')
            ->willReturnSelf();

        $messagesBlockMock = $this->getMockBuilder(\Magento\Framework\View\Element\Messages::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messageCollectionMock = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messageMock = $this->getMockBuilder(\Magento\Framework\Message\MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messageCollectionMock->expects($this->once())
            ->method('getLastAddedMessage')
            ->willReturn($messageMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->willReturnSelf();

        $this->messageManagerMock->expects($this->once())
            ->method('getMessages')
            ->willReturn($messageCollectionMock);

        $layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturnMap(
                [
                    ['import.frame.result', $resultBlockMock],
                    ['messages', $messagesBlockMock]
                ]
            );

        $resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $processingErrorAggregatorMock = $this->getMockBuilder(ProcessingErrorAggregatorInterface::class)
            ->getMockForAbstractClass();

        $this->importModelMock->expects($this->any())
            ->method('getErrorAggregator')
            ->willReturn($processingErrorAggregatorMock);

        $this->importModelMock->expects($this->any())
            ->method('importSource')
            ->willThrowException(new \Exception('Some exeptions'));

        $resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->never())
            ->method('setPath');

        $this->resultFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayoutMock);

        $messagesBlockMock->expects($this->exactly(1))
            ->method('addMessage')
            ->with($messageMock)
            ->willReturn($resultBlockMock);

        $processingErrorAggregatorMock->expects($this->exactly(1))
            ->method('addError');

        $this->assertEquals($resultLayoutMock, $this->start->execute());
    }

    /**
     * Test execute() method
     *
     * Check the case in which the import file was not uploaded.
     */
    public function testFileWasNotUploaded()
    {
        $data = false;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $resultLayoutMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultLayoutMock);

        $this->assertEquals($resultLayoutMock, $this->start->execute());
    }
}
