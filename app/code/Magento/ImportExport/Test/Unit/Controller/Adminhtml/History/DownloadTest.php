<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\History;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRaw;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\ImportExport\Controller\Adminhtml\History\Download
     */
    protected $downloadController;

    /**
     * $var \Magento\ImportExport\Helper\Report|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $reportHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirectFactory;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reportHelper = $this->createPartialMock(
            \Magento\ImportExport\Helper\Report::class,
            ['importFileExists', 'getReportSize', 'getReportOutput']
        );
        $this->reportHelper->expects($this->any())->method('getReportSize')->willReturn(1);
        $this->reportHelper->expects($this->any())->method('getReportOutput')->willReturn('output');
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['get']);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(\Magento\ImportExport\Helper\Report::class)
            ->willReturn($this->reportHelper);
        $this->context = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getObjectManager', 'getResultRedirectFactory']
        );
        $this->fileFactory = $this->createPartialMock(
            \Magento\Framework\App\Response\Http\FileFactory::class,
            ['create']
        );
        $this->resultRaw = $this->createPartialMock(\Magento\Framework\Controller\Result\Raw::class, ['setContents']);
        $this->resultRawFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create']
        );
        $this->resultRawFactory->expects($this->any())->method('create')->willReturn($this->resultRaw);
        $this->redirect = $this->createPartialMock(\Magento\Backend\Model\View\Result\Redirect::class, ['setPath']);

        $this->resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $this->resultRedirectFactory->expects($this->any())->method('create')->willReturn($this->redirect);

        $this->context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->downloadController = $this->objectManagerHelper->getObject(
            \Magento\ImportExport\Controller\Adminhtml\History\Download::class,
            [
                'context' => $this->context,
                'fileFactory' => $this->fileFactory,
                'resultRawFactory' => $this->resultRawFactory,
                'reportHelper' => $this->reportHelper
            ]
        );
    }

    /**
     * Tests download controller with different file names in request.
     *
     * @param string $requestFilename
     * @param string $processedFilename
     * @dataProvider executeDataProvider
     */
    public function testExecute($requestFilename, $processedFilename)
    {
        $this->request->method('getParam')
            ->with('filename')
            ->willReturn($requestFilename);

        $this->reportHelper->method('importFileExists')
            ->with($processedFilename)
            ->willReturn(true);
        $this->resultRaw->expects($this->once())->method('setContents');
        $this->downloadController->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'Normal file name' => ['filename.csv', 'filename.csv'],
            'Relative file name' => ['../../../../../../../../etc/passwd', 'passwd'],
            'Empty file name' => ['', ''],
        ];
    }

    /**
     * Test execute() with not found file
     */
    public function testExecuteFileNotFound()
    {
        $this->request->method('getParam')->with('filename')->willReturn('filename');
        $this->reportHelper->method('importFileExists')->willReturn(false);
        $this->resultRaw->expects($this->never())->method('setContents');
        $this->downloadController->execute();
    }
}
