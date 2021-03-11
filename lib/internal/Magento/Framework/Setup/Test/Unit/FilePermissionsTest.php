<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Test\Unit;

use \Magento\Framework\Setup\FilePermissions;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;

class FilePermissionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\Write
     */
    private $directoryWriteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|State
     */
    private $stateMock;

    /**
     * @var FilePermissions
     */
    private $filePermissions;

    protected function setUp(): void
    {
        $this->directoryWriteMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->stateMock = $this->createMock(State::class);

        $this->filesystemMock
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);
        $this->directoryListMock = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);

        $this->filePermissions = new FilePermissions(
            $this->filesystemMock,
            $this->directoryListMock,
            $this->stateMock
        );
    }

    /**
     * @param string $mageMode
     * @dataProvider modeDataProvider
     */
    public function testGetInstallationWritableDirectories($mageMode)
    {
        $this->setUpDirectoryListInstallation();
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mageMode);

        $expected = [
            BP . '/app/etc',
            BP . '/var',
            BP . '/pub/media',
            BP . '/pub/static',
            BP . '/generated'
        ];

        $this->assertEquals($expected, $this->filePermissions->getInstallationWritableDirectories());
    }

    public function testGetInstallationWritableDirectoriesInProduction()
    {
        $this->setUpDirectoryListInstallationInProduction();
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $expected = [
            BP . '/app/etc',
            BP . '/var',
            BP . '/pub/media',
            BP . '/pub/static'
        ];

        $this->assertEquals($expected, $this->filePermissions->getInstallationWritableDirectories());
    }

    public function testGetApplicationNonWritableDirectories()
    {
        $this->directoryListMock
            ->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(BP . '/app/etc');

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
            ->willReturn(BP . '/app/etc');

        $index = 0;
        foreach ($mockMethods as $mockMethod => $returnValue) {
            $this->directoryWriteMock
                ->expects($this->at($index))
                ->method($mockMethod)
                ->willReturn($returnValue);
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
     * @param string $mageMode
     * @dataProvider modeDataProvider
     * @covers \Magento\Framework\Setup\FilePermissions::getMissingWritableDirectoriesForInstallation
     * @covers \Magento\Framework\Setup\FilePermissions::getMissingWritablePathsForInstallation
     */
    public function testGetMissingWritableDirectoriesAndPathsForInstallation($mageMode)
    {
        $this->setUpDirectoryListInstallation();
        $this->setUpDirectoryWriteInstallation();
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mageMode);

        $expected = [
            BP . '/var',
            BP . '/pub/media',
            BP . '/pub/static',
            BP . '/generated'
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

    public function testGetMissingWritableDirectoriesAndPathsForInstallationInProduction()
    {
        $this->setUpDirectoryListInstallationInProduction();
        $this->setUpDirectoryWriteInstallation();
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $expected = [
            BP . '/var',
            BP . '/pub/media',
            BP . '/pub/static'
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

    public function testGetMissingWritableDirectoriesForDbUpgrade()
    {
        $directoryMethods = ['isExist', 'isDirectory', 'isReadable', 'isWritable'];
        foreach ($directoryMethods as $method) {
            $this->directoryWriteMock->expects($this->exactly(2))
                ->method($method)
                ->willReturn(true);
        }

        $this->assertEmpty($this->filePermissions->getMissingWritableDirectoriesForDbUpgrade());
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
            ->willReturn(BP . '/app/etc');

        $index = 0;
        foreach ($mockMethods as $mockMethod => $returnValue) {
            $this->directoryWriteMock
                ->expects($this->at($index))
                ->method($mockMethod)
                ->willReturn($returnValue);
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

    public function setUpDirectoryListInstallation()
    {
        $this->setUpDirectoryListInstallationInProduction();
        $this->directoryListMock
            ->expects($this->at(4))
            ->method('getPath')
            ->with(DirectoryList::GENERATED)
            ->willReturn(BP . '/generated');
    }

    public function setUpDirectoryListInstallationInProduction()
    {
        $this->directoryListMock
            ->expects($this->at(0))
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(BP . '/app/etc');
        $this->directoryListMock
            ->expects($this->at(1))
            ->method('getPath')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn(BP . '/var');
        $this->directoryListMock
            ->expects($this->at(2))
            ->method('getPath')
            ->with(DirectoryList::MEDIA)
            ->willReturn(BP . '/pub/media');
        $this->directoryListMock
            ->expects($this->at(3))
            ->method('getPath')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn(BP . '/pub/static');
    }

    public function setUpDirectoryWriteInstallation()
    {
        // CONFIG
        $this->directoryWriteMock
            ->expects($this->at(0))
            ->method('isExist')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(1))
            ->method('isDirectory')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(2))
            ->method('isReadable')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(3))
            ->method('isWritable')
            ->willReturn(true);

        // VAR
        $this->directoryWriteMock
            ->expects($this->at(4))
            ->method('isExist')
            ->willReturn(false);

        // MEDIA
        $this->directoryWriteMock
            ->expects($this->at(5))
            ->method('isExist')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(6))
            ->method('isDirectory')
            ->willReturn(false);

        // STATIC_VIEW
        $this->directoryWriteMock
            ->expects($this->at(7))
            ->method('isExist')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(8))
            ->method('isDirectory')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(9))
            ->method('isReadable')
            ->willReturn(true);
        $this->directoryWriteMock
            ->expects($this->at(10))
            ->method('isWritable')
            ->willReturn(false);
    }

    /**
     * @return array
     */
    public function modeDataProvider()
    {
        return [
            [State::MODE_DEFAULT],
            [State::MODE_DEVELOPER],
        ];
    }
}
