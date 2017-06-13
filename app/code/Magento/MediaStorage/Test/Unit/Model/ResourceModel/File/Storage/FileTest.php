<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Test\Unit\Model\ResourceModel\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class FileTest
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\ResourceModel\File\Storage\File
     */
    protected $storageFile;

    /**
     * @var \Magento\MediaStorage\Helper\File\Media|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryReadMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->filesystemMock = $this->getMock(
            \Magento\Framework\Filesystem::class,
            ['getDirectoryRead'],
            [],
            '',
            false
        );
        $this->directoryReadMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\Read::class,
            ['isDirectory', 'readRecursively'],
            [],
            '',
            false
        );

        $this->storageFile = new \Magento\MediaStorage\Model\ResourceModel\File\Storage\File(
            $this->filesystemMock,
            $this->loggerMock
        );
    }

    protected function tearDown()
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
            $this->equalTo(DirectoryList::MEDIA)
        )->will(
            $this->returnValue($this->directoryReadMock)
        );

        $this->directoryReadMock->expects(
            $this->any()
        )->method(
            'isDirectory'
        )->will(
            $this->returnValueMap(
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
            )
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
        sort($paths);
        $this->directoryReadMock->expects(
            $this->once()
        )->method(
            'readRecursively'
        )->with(
            $this->equalTo('/')
        )->will(
            $this->returnValue($paths)
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
