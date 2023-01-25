<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\History;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Controller\Adminhtml\History\Download;
use Magento\ImportExport\Helper\Report;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRaw;

    /**
     * @var Raw|MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Download
     */
    protected $downloadController;

    /**
     * @var Report|MockObject
     */
    protected $reportHelper;

    /**
     * @var FileFactory|MockObject
     */
    protected $fileFactory;

    /**
     * @var RawFactory|MockObject
     */
    protected $resultRawFactory;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reportHelper = $this->createPartialMock(
            Report::class,
            ['importFileExists', 'getReportSize', 'getReportOutput']
        );
        $this->reportHelper->expects($this->any())->method('getReportSize')->willReturn(1);
        $this->reportHelper->expects($this->any())->method('getReportOutput')->willReturn('output');
        $this->objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['get']);
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(Report::class)
            ->willReturn($this->reportHelper);
        $this->context = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getObjectManager', 'getResultRedirectFactory']
        );
        $this->fileFactory = $this->createPartialMock(
            FileFactory::class,
            ['create']
        );
        $this->resultRaw = $this->createPartialMock(Raw::class, ['setContents']);
        $this->resultRawFactory = $this->createPartialMock(
            RawFactory::class,
            ['create']
        );
        $this->resultRawFactory->expects($this->any())->method('create')->willReturn($this->resultRaw);
        $this->redirect = $this->createPartialMock(Redirect::class, ['setPath']);

        $this->resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
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
            Download::class,
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
