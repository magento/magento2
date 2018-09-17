<?php
/**
 * Unit Test for \Magento\Framework\Filesystem\Directory\Write
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Test\Unit\Directory;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Write test.
 */
class WriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * \Magento\Framework\Filesystem\Driver
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $write;

    /**
     * \Magento\Framework\Filesystem\File\ReadFactory
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactory;

    /**
     * @var string
     */
    protected $path;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->driver = $this->getMock(\Magento\Framework\Filesystem\Driver\File::class, [], [], '', false);
        $this->fileFactory = $this->getMock(
            \Magento\Framework\Filesystem\File\WriteFactory::class,
            [],
            [],
            '',
            false
        );
        $this->path = 'PATH/';
        $this->write = new \Magento\Framework\Filesystem\Directory\Write(
            $this->fileFactory,
            $this->driver,
            $this->path,
            'cool-permissions'
        );
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->driver = null;
        $this->fileFactory = null;
        $this->write = null;
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf(
            DriverInterface::class,
            $this->write->getDriver(),
            'getDriver method expected to return instance of Magento\Framework\Filesystem\DriverInterface'
        );
    }

    public function testCreate()
    {
        $this->driver->expects($this->once())->method('isDirectory')->will($this->returnValue(false));
        $this->driver->expects($this->once())->method('createDirectory')->will($this->returnValue(true));

        $this->assertTrue($this->write->create('correct-path'));
    }

    public function testIsWritable()
    {
        $this->driver->expects($this->once())->method('isWritable')->will($this->returnValue(true));
        $this->assertTrue($this->write->isWritable('correct-path'));
    }

    public function testCreateSymlinkTargetDirectoryExists()
    {
        $targetDir = $this->getMockBuilder(WriteInterface::class)
            ->getMock();
        $targetDir->driver = $this->driver;
        $sourcePath = 'source/path/file';
        $destinationDirectory = 'destination/path';
        $destinationFile = $destinationDirectory . '/' . 'file';

        $this->assertIsFileExpectation($sourcePath);
        $this->driver->expects($this->once())
            ->method('getParentDirectory')
            ->with($destinationFile)
            ->willReturn($destinationDirectory);
        $targetDir->expects($this->once())
            ->method('isExist')
            ->with($destinationDirectory)
            ->willReturn(true);
        $targetDir->expects($this->once())
            ->method('getAbsolutePath')
            ->with($destinationFile)
            ->willReturn($this->getAbsolutePath($destinationFile));
        $this->driver->expects($this->once())
            ->method('symlink')
            ->with(
                $this->getAbsolutePath($sourcePath),
                $this->getAbsolutePath($destinationFile),
                $targetDir->driver
            )->willReturn(true);

        $this->assertTrue($this->write->createSymlink($sourcePath, $destinationFile, $targetDir));
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testOpenFileNonWritable()
    {
        $targetPath = '/path/to/target.file';
        $this->driver->expects($this->once())->method('isExists')->willReturn(true);
        $this->driver->expects($this->once())->method('isWritable')->willReturn(false);
        $this->write->openFile($targetPath);
    }

    /**
     * Assert is file expectation
     *
     * @param string $path
     */
    private function assertIsFileExpectation($path)
    {
        $this->driver->expects($this->any())
            ->method('getAbsolutePath')
            ->with($this->path, $path)
            ->willReturn($this->getAbsolutePath($path));
        $this->driver->expects($this->any())
            ->method('isFile')
            ->with($this->getAbsolutePath($path))
            ->willReturn(true);
    }

    /**
     * Returns expected absolute path to file
     *
     * @param string $path
     * @return string
     */
    private function getAbsolutePath($path)
    {
        return $this->path . $path;
    }

    /**
     * @param string $sourcePath
     * @param string $targetPath
     * @param WriteInterface $targetDir
     * @dataProvider getFilePathsDataProvider
     */
    public function testRenameFile($sourcePath, $targetPath, $targetDir)
    {
        if ($targetDir !== null) {
            $targetDir->driver = $this->getMockBuilder(DriverInterface::class)->getMockForAbstractClass();
            $targetDirPath = 'TARGET_PATH/';
            $targetDir->expects($this->once())
                ->method('getAbsolutePath')
                ->with($targetPath)
                ->willReturn($targetDirPath . $targetPath);
            $targetDir->expects($this->once())
                ->method('isExists')
                ->with(dirname($targetPath))
                ->willReturn(false);
            $targetDir->expects($this->once())
                ->method('create')
                ->with(dirname($targetPath));
        }

        $this->driver->expects($this->any())
            ->method('getAbsolutePath')
            ->willReturnMap([
                [$this->path, $sourcePath, null, $this->getAbsolutePath($sourcePath)],
                [$this->path, $targetPath, null, $this->getAbsolutePath($targetPath)],
            ]);
        $this->driver->expects($this->any())
            ->method('isFile')
            ->willReturnMap([
                [$this->getAbsolutePath($sourcePath), true],
                [$this->getAbsolutePath($targetPath), true],
            ]);
        $this->driver->expects($this->any())
            ->method('getParentDirectory')
            ->with($targetPath)
            ->willReturn(dirname($targetPath));
        $this->write->renameFile($sourcePath, $targetPath, $targetDir);
    }

    /**
     * @return array
     */
    public function getFilePathsDataProvider()
    {
        return [
            [
                'path/to/source.file',
                'path/to/target.file',
                null,
            ],
            [
                'path/to/source.file',
                'path/to/target.file',
                $this->getMockBuilder(WriteInterface::class)->getMockForAbstractClass(),
            ],
        ];
    }
}
