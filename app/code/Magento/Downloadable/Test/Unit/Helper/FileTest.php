<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Helper;

use Magento\Downloadable\Helper\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $file;

    /**
     * Core file storage database
     *
     * @var Database|MockObject
     */
    private $coreFileStorageDatabase;

    /**
     * Filesystem object.
     *
     * @var \Magento\Framework\Filesystem|MockObject
     */
    private $filesystem;

    /**
     * Media Directory object (writable).
     *
     * @var WriteInterface|MockObject
     */
    private $mediaDirectory;

    /**
     * @var Context|MockObject
     */
    private $appContext;

    protected function setUp(): void
    {
        $this->mediaDirectory = $this->getMockBuilder(WriteInterface::class)
            ->getMockForAbstractClass();

        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectory);

        $this->coreFileStorageDatabase =
            $this->getMockBuilder(Database::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->appContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getModuleManager',
                    'getLogger',
                    'getRequest',
                    'getUrlBuilder',
                    'getHttpHeader',
                    'getEventManager',
                    'getRemoteAddress',
                    'getCacheConfig',
                    'getUrlEncoder',
                    'getUrlDecoder',
                    'getScopeConfig'
                ]
            )
            ->getMock();
        $this->file = new File(
            $this->appContext,
            $this->coreFileStorageDatabase,
            $this->filesystem
        );
    }

    public function testUploadFromTmp()
    {
        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $uploaderMock->expects($this->once())->method('setAllowRenameFiles');
        $uploaderMock->expects($this->once())->method('setFilesDispersion');
        $this->mediaDirectory->expects($this->once())->method('getAbsolutePath')->willReturn('absPath');
        $uploaderMock->expects($this->once())->method('save')->with('absPath')
            ->willReturn(['file' => 'file.jpg', 'path' => 'absPath']);

        $result = $this->file->uploadFromTmp('tmpPath', $uploaderMock);

        $this->assertArrayNotHasKey('path', $result);
    }
}
