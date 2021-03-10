<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Export\File;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\ImportExport\Controller\Adminhtml\Export\File\Download;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for \Magento\ImportExport\Controller\Adminhtml\Export\File\Download.
 */
class DownloadTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Download
     */
    private $controller;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var FileFactory|MockObject
     */
    private $fileFactoryMock;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    /**
     * @var Read|MockObject
     */
    private $directoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getObjectManager', 'getResultRedirectFactory']
        );
        $this->fileFactoryMock = $this->createPartialMock(FileFactory::class, ['create']);
        $this->fileSystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->directoryMock = $this->createPartialMock(Read::class, ['isFile', 'readFile','isExist']);

        $this->contextMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);

        $this->controller = $this->objectManager->getObject(
            Download::class,
            [
                'context' => $this->contextMock,
                'fileFactory' => $this->fileFactoryMock,
                'filesystem' => $this->fileSystemMock,
            ]
        );
    }

    /**
     * Check download controller behavior.
     *
     * @return void
     */
    public function testExecute(): void
    {
        $fileName = 'customer.csv';
        $path = 'export/' . $fileName;
        $fileContent = 'content';

        $this->processDownloadAction($fileName, $path);
        $this->directoryMock->expects($this->once())->method('readFile')->with($path)->willReturn($fileContent);
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($path, $fileContent, DirectoryList::VAR_DIR)
            ->willReturn($response);

        $this->controller->execute();
    }

    /**
     * Check behavior with incorrect filename.
     *
     * @return void
     */
    public function testExecuteWithEmptyFileName(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Please provide valid export file name');

        $this->requestMock->expects($this->once())->method('getParam')->with('filename')->willReturn('');

        $this->controller->execute();
    }

    /**
     * Check behavior when method throw exception.
     *
     * @return void
     */
    public function testExecuteWithNonExistanceFile(): void
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('There are no export file with such name customer.csv');

        $fileName = 'customer.csv';
        $path = 'export/' . $fileName;

        $this->processDownloadAction($fileName, $path);
        $this->directoryMock->expects($this->once())
            ->method('readFile')
            ->with($path)
            ->willThrowException(new \Exception('Message'));

        $this->controller->execute();
    }

    /**
     * Check that parameter valid and file exist.
     *
     * @param string $fileName
     * @param string $path
     * @return void
     */
    private function processDownloadAction(string $fileName, string $path): void
    {
        $this->requestMock->expects($this->once())->method('getParam')->with('filename')->willReturn($fileName);
        $this->fileSystemMock->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryMock);
        $this->directoryMock->expects($this->once())->method('isExist')->willReturn(true);
        $this->directoryMock->expects($this->once())->method('isFile')->with($path)->willReturn(true);
    }
}
