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
     * @var Database|MockObject
     */
    private $coreFileStorageDatabase;

    /**
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

    /**
     * @var Uploader|MockObject
     */
    private $uploader;

    /**
     * @inheritDoc
     */
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
                ->addMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->appContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
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

        $this->uploader = $this->getMockBuilder(Uploader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    public function testUploadFromTmp()
    {
        $this->uploader->expects($this->once())->method('setAllowRenameFiles');
        $this->uploader->expects($this->once())->method('setFilesDispersion');
        $this->mediaDirectory->expects($this->once())->method('getAbsolutePath')->willReturn('absPath');
        $this->uploader->expects($this->once())->method('save')->with('absPath')
            ->willReturn(['file' => 'file.jpg', 'path' => 'absPath']);

        $result = $this->file->uploadFromTmp('tmpPath', $this->uploader);

        $this->assertArrayNotHasKey('path', $result);
    }

    /**
     * @return void
     */
    public function testUploadFromTmpSuccess(): void
    {
        $tmpPath = __DIR__;
        $data = ['path' => __DIR__ . DIRECTORY_SEPARATOR . 'media', 'file' => 'test_image.jpg'];
        $this->uploader->method('save')->willReturn($data);
        $result = $this->file->uploadFromTmp($tmpPath, $this->uploader);
        $this->assertEquals('test_image.jpg', $result['file']);
        $this->assertEquals(1, count($result));
    }

    /**
     * @return void
     */
    public function testUploadFromTmpFail(): void
    {
        $tmpPath = __DIR__;
        $this->uploader->expects($this->once())->method('save')->willReturn(false);
        $result = $this->file->uploadFromTmp($tmpPath, $this->uploader);
        $this->assertEquals(false, $result);
    }
}
