<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Import;

class ValidateTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\ImportExport\Controller\Adminhtml\Import\Validate
     */
    private $validate;

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

        $this->validate = new \Magento\ImportExport\Controller\Adminhtml\Import\Validate(
            $this->contextMock,
            $this->reportProcessorMock,
            $this->historyMock,
            $this->reportHelperMock
        );
    }

    /**
     * Test execute() method
     *
     * Check the case in which no data was posted.
     */
    public function testNoDataWasPosted()
    {
        $data = null;

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);

        $resultBlock = $this->getMockBuilder(\Magento\ImportExport\Block\Adminhtml\Import\Frame\Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/index');

        $this->resultFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT, [], $resultLayoutMock],
                [\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT, [], $resultRedirectMock],
            ]);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with(__('Sorry, but the data is invalid or the file is not uploaded.'));

        $this->assertEquals($resultRedirectMock, $this->validate->execute());
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
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $resultBlock = $this->getMockBuilder(\Magento\ImportExport\Block\Adminhtml\Import\Frame\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultBlock->expects($this->once())
            ->method('addError')
            ->with(__('The file was not uploaded.'));

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(\Magento\Framework\View\Result\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayoutMock);

        $this->assertEquals($resultLayoutMock, $this->validate->execute());
    }
}
