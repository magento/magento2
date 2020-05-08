<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Layout;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result;
use Magento\ImportExport\Controller\Adminhtml\Import\Validate;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Report\ReportProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ReportProcessorInterface|MockObject
     */
    private $reportProcessorMock;

    /**
     * @var History|MockObject
     */
    private $historyMock;

    /**
     * @var Report|MockObject
     */
    private $reportHelperMock;

    /**
     * @var Validate
     */
    private $validate;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getPostValue',
                'isPost',
            ])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock = $this->getMockBuilder(Context::class)
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
            ReportProcessorInterface::class
        )
            ->getMockForAbstractClass();

        $this->historyMock = $this->getMockBuilder(History::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportHelperMock = $this->getMockBuilder(Report::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validate = new Validate(
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

        $resultBlock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('adminhtml/*/index');

        $this->resultFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [ResultFactory::TYPE_LAYOUT, [], $resultLayoutMock],
                [ResultFactory::TYPE_REDIRECT, [], $resultRedirectMock],
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

        $resultBlock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultBlock->expects($this->once())
            ->method('addError')
            ->with(__('The file was not uploaded.'));

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('import.frame.result')
            ->willReturn($resultBlock);

        $resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultLayoutMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_LAYOUT)
            ->willReturn($resultLayoutMock);

        $this->assertEquals($resultLayoutMock, $this->validate->execute());
    }
}
