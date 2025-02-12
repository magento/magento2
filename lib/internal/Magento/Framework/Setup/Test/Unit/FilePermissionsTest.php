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

    /**
     * @inheritDoc
     */
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
     *
     * @return void
     * @dataProvider modeDataProvider
     */
    public function testGetInstallationWritableDirectories($mageMode): void
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
            BP . '/pub/static'
        ];

        $this->assertEquals($expected, $this->filePermissions->getInstallationWritableDirectories());
    }

    /**
     * @return void
     */
    public function testGetInstallationWritableDirectoriesInProduction(): void
    {
        $this->setUpDirectoryListInstallationInProduction();
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);

        $expected = [
            BP . '/app/etc',
            BP . '/var',
            BP . '/pub/media'
        ];

        $this->assertEquals($expected, $this->filePermissions->getInstallationWritableDirectories());
    }

    /**
     * @return void
     */
    public function testGetApplicationNonWritableDirectories(): void
    {
        $this->directoryListMock
            ->expects($this->once())
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(BP . '/app/etc');

        $expected = [BP . '/app/etc'];
        $this->assertEquals($expected, $this->filePermissions->getApplicationNonWritableDirectories());
    }

    /**
     * @return void
     */
    public function testGetInstallationCurrentWritableDirectories(): void
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
     *
     * @return void
     * @dataProvider getApplicationCurrentNonWritableDirectoriesDataProvider
     */
    public function testGetApplicationCurrentNonWritableDirectories(array $mockMethods, array $expected): void
    {
        $this->directoryListMock
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(BP . '/app/etc');

        foreach ($mockMethods as $mockMethod => $returnValue) {
            $this->directoryWriteMock
                ->method($mockMethod)
                ->willReturnOnConsecutiveCalls($returnValue);
        }

        $this->filePermissions->getApplicationNonWritableDirectories();
        $this->assertEquals($expected, $this->filePermissions->getApplicationCurrentNonWritableDirectories());
    }

    /**
     * @return array
     */
    public static function getApplicationCurrentNonWritableDirectoriesDataProvider(): array
    {
        return [
            [
                [
                    'isExist' => true,
                    'isDirectory' => true,
                    'isReadable' => true,
                    'isWritable' => false
                ],
                [BP . '/app/etc']
            ],
            [['isExist' => false], []],
            [['isExist' => true, 'isDirectory' => false], []],
            [['isExist' => true, 'isDirectory' => true, 'isReadable' => true, 'isWritable' => true], []]
        ];
    }

    /**
     * @param string $mageMode
     *
     * @return void
     * @dataProvider modeDataProvider
     * @covers \Magento\Framework\Setup\FilePermissions::getMissingWritableDirectoriesForInstallation
     * @covers \Magento\Framework\Setup\FilePermissions::getMissingWritablePathsForInstallation
     */
    public function testGetMissingWritableDirectoriesAndPathsForInstallation($mageMode): void
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

    /**
     * @return void
     */
    public function testGetMissingWritableDirectoriesAndPathsForInstallationInProduction(): void
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

    /**
     * @return void
     */
    public function testGetMissingWritableDirectoriesForDbUpgrade(): void
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
     *
     * @return void
     * @dataProvider getUnnecessaryWritableDirectoriesForApplicationDataProvider
     */
    public function testGetUnnecessaryWritableDirectoriesForApplication(array $mockMethods, array $expected): void
    {
        $this->directoryListMock
            ->method('getPath')
            ->with(DirectoryList::CONFIG)
            ->willReturn(BP . '/app/etc');

        foreach ($mockMethods as $mockMethod => $returnValue) {
            $this->directoryWriteMock
                ->method($mockMethod)
                ->willReturnOnConsecutiveCalls($returnValue);
        }

        $this->assertEquals(
            $expected,
            array_values($this->filePermissions->getUnnecessaryWritableDirectoriesForApplication())
        );
    }

    /**
     * @return array
     */
    public static function getUnnecessaryWritableDirectoriesForApplicationDataProvider(): array
    {
        return [
            [['isExist' => true, 'isDirectory' => true, 'isReadable' => true, 'isWritable' => false], []],
            [['isExist' => false], [BP . '/app/etc']]
        ];
    }

    /**
     * @return void
     */
    public function setUpDirectoryListInstallation(): void
    {
        $this->directoryListMock
            ->method('getPath')
            ->willReturnCallback(
                function ($arg1) {
                    if ($arg1 == DirectoryList::CONFIG) {
                        return BP . '/app/etc';
                    } elseif ($arg1 == DirectoryList::VAR_DIR) {
                        return BP . '/var';
                    } elseif ($arg1 == DirectoryList::MEDIA) {
                        return BP . '/pub/media';
                    } elseif ($arg1 == DirectoryList::GENERATED) {
                        return BP . '/generated';
                    } elseif ($arg1 == DirectoryList::STATIC_VIEW) {
                        return BP . '/pub/static';
                    }
                }
            );
    }

    /**
     * @return void
     */
    public function setUpDirectoryListInstallationInProduction(): void
    {
        $this->directoryListMock
            ->method('getPath')
            ->willReturnCallback(
                function ($arg1) {
                    if ($arg1 == DirectoryList::CONFIG) {
                        return BP . '/app/etc';
                    } elseif ($arg1 == DirectoryList::VAR_DIR) {
                        return BP . '/var';
                    } elseif ($arg1 == DirectoryList::MEDIA) {
                        return BP . '/pub/media';
                    }
                }
            );
    }

    /**
     * @return void
     */
    public function setUpDirectoryWriteInstallation(): void
    {
        $this->directoryWriteMock
            ->method('isExist')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return true;
                } elseif ($callCount === 2) {
                    return false;
                } elseif ($callCount === 3) {
                    return true;
                }
            });
        $this->directoryWriteMock
            ->method('isWritable')
            ->willReturn(true);
        $this->directoryWriteMock
            ->method('isReadable')
            ->willReturn(true);
        $this->directoryWriteMock
            ->method('isDirectory')
            ->willReturnOnConsecutiveCalls(true, false);
    }

    /**
     * @return array
     */
    public static function modeDataProvider(): array
    {
        return [
            [State::MODE_DEFAULT],
            [State::MODE_DEVELOPER]
        ];
    }
}
