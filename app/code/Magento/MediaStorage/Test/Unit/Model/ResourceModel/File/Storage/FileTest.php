<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Test\Unit\Model\ResourceModel\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Helper\File\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FileTest extends TestCase
{
    /**
     * @var File
     */
    private $fileIoMock;

    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $storageFile;

    /**
     * @var Media|MockObject
     */
    protected $loggerMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var Read|MockObject
     */
    protected $directoryReadMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryRead']);
        $this->directoryReadMock = $this->createPartialMock(
            Read::class,
            ['isDirectory', 'readRecursively']
        );

        $this->fileIoMock = $this->createPartialMock(File::class, ['getPathInfo']);

        $objectManager = new ObjectManager($this);

        $this->storageFile = $objectManager->getObject(
            \Magento\MediaStorage\Model\ResourceModel\File\Storage\File::class,
            [
                'filesystem' => $this->filesystemMock,
                'log' => $this->loggerMock,
                'fileIo' => $this->fileIoMock
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->storageFile);
    }

    /**
     * test get storage data
     */
    public function testGetStorageData()
    {
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            DirectoryList::MEDIA
        )->willReturn(
            $this->directoryReadMock
        );

        $this->directoryReadMock->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->willReturnMap(
            [
                ['/', true],
                ['folder_one', true],
                ['file_three.txt', false],
                ['folder_one/.svn', false],
                ['folder_one/file_one.txt', false],
                ['folder_one/folder_two', true],
                ['folder_one/folder_two/.htaccess', false],
                ['folder_one/folder_two/file_two.txt', false],
            ]
        );

        $paths = [
            'folder_one',
            'file_three.txt',
            'folder_one/.svn',
            'folder_one/file_one.txt',
            'folder_one/folder_two',
            'folder_one/folder_two/.htaccess',
            'folder_one/folder_two/file_two.txt',
        ];

        $pathInfos = array_map(
            function ($path) {
                return [$path, pathinfo($path)];
            },
            $paths
        );

        $this->fileIoMock->expects(
            $this->any()
        )->method(
            'getPathInfo'
        )->willReturnMap($pathInfos);

        sort($paths);
        $this->directoryReadMock->expects(
            $this->once()
        )->method(
            'readRecursively'
        )->with(
            '/'
        )->willReturn(
            $paths
        );

        $expected = [
            'files' => ['file_three.txt', 'folder_one/file_one.txt', 'folder_one/folder_two/file_two.txt'],
            'directories' => [
                ['name' => 'folder_one', 'path' => '/'],
                ['name' => 'folder_two', 'path' => 'folder_one'],
            ],
        ];
        $actual = $this->storageFile->getStorageData();

        $this->assertEquals($expected, $actual);
    }
}
