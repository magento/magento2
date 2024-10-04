<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Test\Unit\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\RemoteStorage\Filesystem;
use Magento\RemoteStorage\Model\Synchronizer;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Filesystem\DriverPool;
use Magento\RemoteStorage\Driver\DriverPool as RemoteDriverPool;

/**
 * @see Synchronizer
 */
class SynchronizerTest extends TestCase
{
    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var Filesystem
     */
    private $filesystemMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->synchronizer = new Synchronizer(
            $this->filesystemMock
        );
    }

    /**
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function testExecute(): void
    {
        $this->filesystemMock->method('getDirectoryCodes')
            ->willReturn(['test', 'import_export']);

        $localDriver = $this->createMock(DriverInterface::class);
        $remoteDriver = $this->createMock(DriverInterface::class);

        $localDirectory = $this->createMock(WriteInterface::class);
        $localDirectory->method('getDriver')
            ->willReturn($localDriver);
        $remoteDirectory = $this->createMock(WriteInterface::class);
        $remoteDirectory->method('getDriver')
            ->willReturn($remoteDriver);

        $this->filesystemMock->expects(self::exactly(2))
            ->method('getDirectoryWrite')
            ->willReturnMap([
                ['test', DriverPool::FILE, $localDirectory],
                ['test', RemoteDriverPool::REMOTE, $remoteDirectory]
            ]);
        $localDirectory->method('getAbsolutePath')
            ->willReturnMap([
                [null, __DIR__ . '/_files/test']
            ]);
        $localDirectory->method('getRelativePath')
            ->willReturnCallback(function ($arg) {
                return str_replace(__DIR__, '', $arg);
            });
        $remoteDirectory->expects(self::exactly(2))
            ->method('isExist')
            ->willReturnMap([
                [
                    'remote:/_files/test/root_file.txt',
                    false
                ],
                [
                    'remote:/_files/test/.dot_directory/child_file.txt',
                    true
                ]
            ]);
        $remoteDirectory->method('getAbsolutePath')
            ->willReturnCallback(function ($arg) {
                return 'remote:' . $arg;
            });

        $localDriver->expects(self::once())
            ->method('copy')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($remoteDriver) {
                if ($arg1 == __DIR__ . '/_files/test/root_file.txt' &&
                $arg2 == 'remote:/_files/test/root_file.txt' && $arg3 == $remoteDriver) {
                    return null;
                }
            });

        self::assertSame(
            [
                '/_files/test/root_file.txt',
                '/_files/test/.dot_directory'
            ],
            iterator_to_array($this->synchronizer->execute(), false)
        );
    }
}
