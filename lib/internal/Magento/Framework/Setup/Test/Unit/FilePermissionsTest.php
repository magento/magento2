<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Test\Unit;

use \Magento\Framework\Setup\FilePermissions;
use Magento\Framework\App\Filesystem\DirectoryList;

class FilePermissionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Write
     */
    private $directoryWriteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Driver\File
     */
    private $driverFileMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\OsInfo
     */
    private $osInfoMock;

    /**
     * @var FilePermissions
     */
    private $filePermissions;

    public function setUp()
    {
        $this->directoryWriteMock = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->driverFileMock = $this->getMock('Magento\Framework\Filesystem\Driver\File', [], [], '', false);
        $this->osInfoMock = $this->getMock('Magento\Framework\OsInfo', [], [], '', false);

        $this->filesystemMock
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($this->directoryWriteMock));
        $this->directoryListMock = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);

        $this->filePermissions = new FilePermissions(
            $this->filesystemMock,
            $this->directoryListMock,
            $this->driverFileMock,
            $this->osInfoMock
        );
    }

    public function testGetInstallationWritableDirectories()
    {
        $this->setUpDirectoryListInstallation();

        $expected = [
            BP . '/app/etc',
            BP . '/var',
            BP . '/pub/media',
            BP . '/pub/static',
        ];

        $this->assertEquals($expected, $this->filePermissions->getInstallationWritableDirectories());
    }

    public function testGetApplicationNonWritableDirectories()
    {
        $this->directoryListMock
            ->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->will($this->returnValue(BP . '/app/etc'));

        $expected = [BP . '/app/etc'];
        $this->assertEquals($expected, $this->filePermissions->getApplicationNonWritableDirectories());
    }

    public function testGetInstallationCurrentWritableDirectories()
    {
        $this->setUpDirectoryListInstallation();
        $this->setUpDirectoryWriteInstallation();

        $expected = [
            BP . '/app/etc',
        ];
        $this->filePermissions->getInstallationWritableDirectories();
        $this->assertEquals($expected, $this->filePermissions->getInstallationCurrentWritableDirectories());
    }

    /**
     * @param array $mockMethods
     * @param array $expected
     * @dataProvider getApplicationCurrentNonWritableDirectoriesDataProvider
     */
    public function testGetApplicationCurrentNonWritableDirectories(array $mockMethods, array $expected)
    {
        $this->directoryListMock
            ->expects($this->at(0))
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->will($this->returnValue(BP . '/app/etc'));

        $index = 0;
        foreach ($mockMethods as $mockMethod => $returnValue) {
            $this->directoryWriteMock
                ->expects($this->at($index))
                ->method($mockMethod)
                ->will($this->returnValue($returnValue));
            $index += 1;
        }

        $this->filePermissions->getApplicationNonWritableDirectories();
        $this->assertEquals($expected, $this->filePermissions->getApplicationCurrentNonWritableDirectories());
    }

    /**
     * @return array
     */
    public function getApplicationCurrentNonWritableDirectoriesDataProvider()
    {
        return [
            [
                [
                    'isExist' => true,
                    'isDirectory' => true,
                    'isReadable' => true,
                    'isWritable' => false
                ],
                [BP . '/app/etc'],
            ],
            [['isExist' => false], []],
            [['isExist' => true, 'isDirectory' => false], []],
            [['isExist' => true, 'isDirectory' => true, 'isReadable' => true, 'isWritable' => true], []],
        ];
    }

    /**
     * @covers \Magento\Framework\FilePermissions::getMissingWritableDirectoriesForInstallation
     * @covers \Magento\Framework\FilePermissions::getMissingWritablePathsForInstallation
     */
    public function testGetMissingWritableDirectoriesAndPathsForInstallation()
    {
        $this->setUpDirectoryListInstallation();
        $this->setUpDirectoryWriteInstallation();

        $expected = [
            BP . '/var',
            BP . '/pub/media',
            BP . '/pub/static',
        ];

        $this->assertEquals(
            $expected,
            array_values($this->filePermissions->getMissingWritableDirectoriesForInstallation())
        );

        $this->assertEquals(
            $expected,
            array_values($this->filePermissions->getMissingWritablePathsForInstallation())
        );
    }

    /**
     * @param array $mockMethods
     * @param array $expected
     * @dataProvider getUnnecessaryWritableDirectoriesForApplicationDataProvider
     */
    public function testGetUnnecessaryWritableDirectoriesForApplication(array $mockMethods, array $expected)
    {
        $this->directoryListMock
            ->expects($this->at(0))
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->will($this->returnValue(BP . '/app/etc'));

        $index = 0;
        foreach ($mockMethods as $mockMethod => $returnValue) {
            $this->directoryWriteMock
                ->expects($this->at($index))
                ->method($mockMethod)
                ->will($this->returnValue($returnValue));
            $index += 1;
        }

        $this->assertEquals(
            $expected,
            array_values($this->filePermissions->getUnnecessaryWritableDirectoriesForApplication())
        );
    }

    /**
     * @return array
     */
    public function getUnnecessaryWritableDirectoriesForApplicationDataProvider()
    {
        return [
            [['isExist' => true, 'isDirectory' => true, 'isReadable' => true, 'isWritable' => false], []],
            [['isExist' => false], [BP . '/app/etc']],
        ];
    }

    /**
     * Directories have executable permission, not Windows
     */
    public function testCheckDirectoryPermissionForCLIUser()
    {
        $this->directoryListMock->expects($this->once())->method('getPath')->willReturn('/var/generation');
        $this->driverFileMock->expects($this->once())
            ->method('readDirectory')
            ->willReturn(['/var/generation/Composer', '/var/gen/Magento']);
        // Should never check for OS if executable
        $this->osInfoMock->expects($this->never())->method('isWindows');
        $this->assertTrue($this->filePermissions->checkDirectoryPermissionForCLIUser());
    }

    /**
     * Directories do not have executable permissions, is Windows
     */
    public function testCheckDirectoryPermissionForCLIUserWin()
    {
        $this->directoryListMock->expects($this->once())->method('getPath')->willReturn('/var/generationNotExec');
        $this->driverFileMock->expects($this->once())
            ->method('readDirectory')
            ->willReturn(['/var/generation/ComposerNotExec', '/var/generation/MagentoNotExec']);
        // Contains a 'NotExec', so is_executable will return false, isWindows should be called once for each
        // directory (including parent) and return true
        $this->osInfoMock->expects($this->exactly(3))->method('isWindows')->willReturn(true);
        $this->assertTrue($this->filePermissions->checkDirectoryPermissionForCLIUser());
    }

    /**
     * One directory does not have executable permission, is not Windows
     */
    public function testCheckDirectoryPermissionForCLIUserNotExecutable()
    {
        $this->directoryListMock->expects($this->once())->method('getPath')->willReturn('/var/generation');
        $this->driverFileMock->expects($this->once())
            ->method('readDirectory')
            ->willReturn(['/var/generation/ComposerNotExec', '/var/gen/Magento']);
        // Contains a 'NotExec', so is_executable will return false, isWindows should be called and return false
        $this->osInfoMock->expects($this->once())->method('isWindows')->willReturn(false);
        $this->assertFalse($this->filePermissions->checkDirectoryPermissionForCLIUser());
    }

    /*
     * exec directory, unix
     * non-exec directory, windows
     * non-exec directory, unix
     */

    public function setUpDirectoryListInstallation()
    {
        $this->directoryListMock
            ->expects($this->at(0))
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->will($this->returnValue(BP . '/app/etc'));
        $this->directoryListMock
            ->expects($this->at(1))
            ->method('getPath')
            ->with(DirectoryList::VAR_DIR)
            ->will($this->returnValue(BP . '/var'));
        $this->directoryListMock
            ->expects($this->at(2))
            ->method('getPath')
            ->with(DirectoryList::MEDIA)
            ->will($this->returnValue(BP . '/pub/media'));
        $this->directoryListMock
            ->expects($this->at(3))
            ->method('getPath')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue(BP . '/pub/static'));
    }

    public function setUpDirectoryWriteInstallation()
    {
        // CONFIG
        $this->directoryWriteMock
            ->expects($this->at(0))
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(1))
            ->method('isDirectory')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(2))
            ->method('isReadable')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(3))
            ->method('isWritable')
            ->will($this->returnValue(true));

        // VAR
        $this->directoryWriteMock
            ->expects($this->at(4))
            ->method('isExist')
            ->will($this->returnValue(false));

        // MEDIA
        $this->directoryWriteMock
            ->expects($this->at(5))
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(6))
            ->method('isDirectory')
            ->will($this->returnValue(false));

        // STATIC_VIEW
        $this->directoryWriteMock
            ->expects($this->at(7))
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(8))
            ->method('isDirectory')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(9))
            ->method('isReadable')
            ->will($this->returnValue(true));
        $this->directoryWriteMock
            ->expects($this->at(10))
            ->method('isWritable')
            ->will($this->returnValue(false));
    }
}

namespace Magento\Framework\Setup;

/**
 * Overriding the built-in PHP function is_dir, always returns true,
 * allows unit test of this code without having to setup special directories.
 *
 * @param string $filename
 * @return true
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_dir($filename)
{
    return true;
}

/**
 * Overriding the built-in PHP function is_readable, always returns true,
 * allows unit test of this code without having to setup special directories.
 *
 * @param string $filename
 * @return true
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_readable($filename)
{
    return true;
}

/**
 * Overriding the built-in PHP function is_executable, will return false if directory name contains 'NotExec'
 * Allows unit test of this code without having to setup a special directory with non-executable permission.
 *
 * @param string $filename
 * @return bool
 */
function is_executable($filename)
{
    if (strpos($filename, 'NotExec') !== false) {
        return false;
    }
    return true;
}
