<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Controller\Adminhtml\Export\File;

use Magento\Framework\Filesystem;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Controller\Adminhtml\Export\File\Download;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
    private $readMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);

        $this->contextMock = $this->createPartialMock(
            Context::class,
            ['getRequest', 'getObjectManager', 'getResultRedirectFactory']
        );
        $this->fileFactoryMock = $this->createPartialMock(FileFactory::class, ['create']);
        $this->fileSystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);
        $this->requestMock = $this->createPartialMock(Http::class, ['getParam']);
        $this->readMock = $this->createPartialMock(Read::class, ['isFile', 'readFile']);

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
        $this->readMock->expects($this->once())->method('readFile')->with($path)->willReturn($fileContent);
        $response = $this->createMock(ResponseInterface::class);
        $this->fileFactoryMock->expects($this->once())
            ->method('create')
            ->with($path, $fileContent, DirectoryList::VAR_DIR)
            ->willReturn($response);

        $this->controller->execute();
    }

    /**
     * Check behavior with incorrect filename.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please provide valid export file name
     * @return void
     */
    public function testExecuteWithEmptyFileName(): void
    {
        $this->requestMock->expects($this->once())->method('getParam')->with('filename')->willReturn('');

        $this->controller->execute();
    }

    /**
     * Check behavior when method throw exception.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There are no export file with such name customer.csv
     * @return void
     */
    public function testExecuteWithNonExistanceFile(): void
    {
        $fileName = 'customer.csv';
        $path = 'export/' . $fileName;

        $this->processDownloadAction($fileName, $path);
        $this->readMock->expects($this->once())
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
        $this->fileSystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->readMock);
        $this->readMock->expects($this->once())->method('isFile')->with($path)->willReturn(true);
    }
}
