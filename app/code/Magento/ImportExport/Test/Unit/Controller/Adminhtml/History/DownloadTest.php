<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\History;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DownloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRaw;

    /**
     * @var \Magento\Framework\Controller\Result\Raw|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\ImportExport\Controller\Adminhtml\History\Download
     */
    protected $downloadController;

    /**
     * $var \Magento\ImportExport\Helper\Report|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->request = $this->getMock(
            \Magento\Framework\App\Request\Http::class,
            ['getParam'],
            [],
            '',
            false
        );
        $this->request->expects($this->any())->method('getParam')->with('filename')->willReturn('filename');
        $this->reportHelper = $this->getMock(
            \Magento\ImportExport\Helper\Report::class,
            ['importFileExists', 'getReportSize', 'getReportOutput'],
            [],
            '',
            false
        );
        $this->reportHelper->expects($this->any())->method('getReportSize')->willReturn(1);
        $this->reportHelper->expects($this->any())->method('getReportOutput')->willReturn('output');
        $this->objectManager = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['get'],
            [],
            '',
            false
        );
        $this->objectManager->expects($this->any())
            ->method('get')
            ->with(\Magento\ImportExport\Helper\Report::class)
            ->willReturn($this->reportHelper);
        $this->context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getObjectManager', 'getResultRedirectFactory'],
            [],
            '',
            false
        );
        $this->fileFactory = $this->getMock(
            \Magento\Framework\App\Response\Http\FileFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultRaw = $this->getMock(
            \Magento\Framework\Controller\Result\Raw::class,
            ['setContents'],
            [],
            '',
            false
        );
        $this->resultRawFactory = $this->getMock(
            \Magento\Framework\Controller\Result\RawFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultRawFactory->expects($this->any())->method('create')->willReturn($this->resultRaw);
        $this->redirect = $this->getMock(
            \Magento\Backend\Model\View\Result\Redirect::class,
            ['setPath'],
            [],
            '',
            false
        );

        $this->resultRedirectFactory = $this->getMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create'],
            [],
            '',
            false
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
     * Test execute()
     */
    public function testExecute()
    {
        $this->reportHelper->expects($this->any())->method('importFileExists')->willReturn(true);
        $this->resultRaw->expects($this->once())->method('setContents');
        $this->downloadController->execute();
    }

    /**
     * Test execute() with not found file
     */
    public function testExecuteFileNotFound()
    {
        $this->reportHelper->expects($this->any())->method('importFileExists')->willReturn(false);
        $this->resultRaw->expects($this->never())->method('setContents');
        $this->downloadController->execute();
    }
}
