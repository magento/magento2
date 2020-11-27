<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Setup\FilePermissions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilePermissionsTest extends TestCase
{
    /**
     * @var MockObject|Write
     */
    private $directoryWriteMock;

    /**
     * @var MockObject|Filesystem
     */
    private $filesystemMock;

    /**
     * @var MockObject|DirectoryList
     */
    private $directoryListMock;

    /**
     * @var MockObject|State
     */
    private $stateMock;

    /**
     * @var FilePermissions
     */
    private $filePermissions;

    protected function setUp(): void
    {
        $this->directoryWriteMock = $this->createMock(Write::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->stateMock = $this->createMock(State::class);

        $this->filesystemMock
            ->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);
        $this->directoryListMock = $this->createMock(DirectoryList::class);

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
            BP . '/generated',
            BP . '/pub/static',
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
            $index++;
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
            BP . '/generated',
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

    public function testGetMissingWritableDirectoriesAndPathsForInstallationInProduction()
    {
        $this->setUpDirectoryListInstallationInProduction();
        $this->setUpDirectoryWriteInstallation();
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $expected = [
            BP . '/var',
            BP . '/pub/media'
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
            $index++;
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
            ->expects($this->at(3))
            ->method('getPath')
            ->with(DirectoryList::GENERATED)
            ->willReturn(BP . '/generated');
        $this->directoryListMock
            ->expects($this->at(4))
            ->method('getPath')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn(BP . '/pub/static');
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
