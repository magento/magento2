<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\SubresourceIntegrity\Storage;

use Magento\Csp\Model\SubresourceIntegrity\Storage\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for File storage
 */
class FileTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private Filesystem $filesystem;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var Json|MockObject
     */
    private Json $serializer;

    /**
     * @var File
     */
    private File $fileStorage;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->serializer = $this->createMock(Json::class);

        $this->fileStorage = new File(
            $this->filesystem,
            $this->logger,
            $this->serializer
        );
    }

    // =========================================================================
    // load() tests
    // =========================================================================

    /**
     * Test loading SRI hashes from both individual and merged locations
     */
    public function testLoadCombinesIndividualAndMergedHashes(): void
    {
        $context = 'frontend';
        $individualHashes = ['frontend/path/file.js' => 'sha256-individual'];
        $mergedHashes = ['_cache/merged/abc123.min.js' => 'sha256-merged'];
        $combinedHashes = array_merge($individualHashes, $mergedHashes);

        $staticDir = $this->createMock(ReadInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $staticDir->method('isFile')
            ->willReturnCallback(function ($path) {
                return in_array($path, ['frontend/sri-hashes.json', '_cache/merged/sri-hashes.json']);
            });

        $staticDir->method('readFile')
            ->willReturnCallback(function ($path) use ($individualHashes, $mergedHashes) {
                if ($path === 'frontend/sri-hashes.json') {
                    return json_encode($individualHashes);
                }
                if ($path === '_cache/merged/sri-hashes.json') {
                    return json_encode($mergedHashes);
                }
                return '';
            });

        $this->serializer->method('unserialize')
            ->willReturnCallback(function ($data) {
                $decoded = json_decode($data, true);
                return is_array($decoded) ? $decoded : [];
            });

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($combinedHashes)
            ->willReturn(json_encode($combinedHashes));

        $result = $this->fileStorage->load($context);

        $this->assertEquals(json_encode($combinedHashes), $result);
    }

    /**
     * Test loading when files don't exist returns null
     */
    public function testLoadReturnsNullWhenFilesDoNotExist(): void
    {
        $context = 'frontend';

        $staticDir = $this->createMock(ReadInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $staticDir->method('isFile')
            ->willReturn(false);

        $result = $this->fileStorage->load($context);

        $this->assertNull($result);
    }

    /**
     * Test that filesystem exceptions are caught and logged
     */
    public function testLoadHandlesFilesystemException(): void
    {
        $context = 'frontend';

        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->willThrowException(new \Magento\Framework\Exception\FileSystemException(__('Error')));

        $this->logger->expects($this->once())
            ->method('warning');

        $result = $this->fileStorage->load($context);

        $this->assertNull($result);
    }

    /**
     * Test that invalid JSON is handled gracefully
     */
    public function testLoadHandlesInvalidJson(): void
    {
        $context = 'frontend';

        $staticDir = $this->createMock(ReadInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $callCount = 0;
        $staticDir->method('isFile')
            ->willReturnCallback(function () use (&$callCount) {
                $callCount++;
                return $callCount === 1;
            });

        $staticDir->expects($this->once())
            ->method('readFile')
            ->with('frontend/sri-hashes.json')
            ->willReturn('invalid json');

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with('invalid json')
            ->willThrowException(new \InvalidArgumentException('Invalid JSON'));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('frontend/sri-hashes.json'));

        $result = $this->fileStorage->load($context);

        $this->assertNull($result);
    }

    // =========================================================================
    // remove() tests
    // =========================================================================

    /**
     * Test removing SRI hash file
     */
    public function testRemoveDeletesFile(): void
    {
        $context = 'frontend';

        $staticDir = $this->createMock(WriteInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $staticDir->expects($this->once())
            ->method('delete')
            ->with('frontend/sri-hashes.json')
            ->willReturn(true);

        $result = $this->fileStorage->remove($context);

        $this->assertTrue($result);
    }

    // =========================================================================
    // save() tests — driver-level assertions
    //
    // saveHashesToFile() uses low-level DriverInterface methods (fileOpen,
    // fileLock, fileSeek, stat, fileRead, fileWrite, fileUnlock, fileClose)
    // rather than WriteInterface::writeFile(). All save() tests must mock the
    // driver returned by WriteInterface::getDriver().
    // =========================================================================

    /**
     * Build a WriteInterface mock that delegates to the given DriverInterface mock.
     *
     * @param DriverInterface|MockObject $driver
     * @return WriteInterface|MockObject
     */
    private function buildWriteDirMock(MockObject $driver): MockObject
    {
        $staticDir = $this->createMock(WriteInterface::class);
        $staticDir->method('getDriver')->willReturn($driver);
        $staticDir->method('getAbsolutePath')->willReturnArgument(0);
        $staticDir->method('create')->willReturn(true);
        return $staticDir;
    }

    /**
     * Build a DriverInterface mock with sensible defaults for a successful empty-file save.
     *
     * @param resource $resource  PHP file handle to return from fileOpen()
     * @return DriverInterface|MockObject
     */
    private function buildDriverMock($resource): MockObject
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->method('getParentDirectory')->willReturn('some/dir');
        $driver->method('fileOpen')->willReturn($resource);
        $driver->method('fileLock')->willReturn(true);
        $driver->method('fileSeek')->willReturn(0);
        $driver->method('fileWrite')->willReturn(1);
        $driver->method('fileUnlock')->willReturn(true);
        $driver->method('fileClose')->willReturn(true);
        return $driver;
    }

    /**
     * Test save() routes merged paths to _cache/merged/sri-hashes.json
     * and individual paths to the context-specific sri-hashes.json.
     *
     * fileOpen() must be called for both destination files.
     */
    public function testSaveSeparatesMergedAndIndividualFiles(): void
    {
        $context = 'frontend';
        $data = [
            'frontend/path/file.js'      => 'sha256-individual',
            '_cache/merged/abc123.min.js' => 'sha256-merged',
        ];

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $staticDir = $this->buildWriteDirMock($driver);

        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);

        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $fileOpenPaths = [];
        $driver->expects($this->exactly(2))
            ->method('fileOpen')
            ->willReturnCallback(function ($path) use (&$fileOpenPaths, $resource) {
                $fileOpenPaths[] = $path;
                return $resource;
            });

        $result = $this->fileStorage->save(json_encode($data), $context);

        $this->assertTrue($result);
        $this->assertContains('_cache/merged/sri-hashes.json', $fileOpenPaths);
        $this->assertContains('frontend/sri-hashes.json', $fileOpenPaths);

        fclose($resource);
    }

    /**
     * Test save() acquires LOCK_EX before reading the existing file.
     */
    public function testSaveAcquiresExclusiveLock(): void
    {
        $context = 'frontend';
        $data = ['js/app.js' => 'sha256-ABC'];

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $staticDir = $this->buildWriteDirMock($driver);

        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);
        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $driver->expects($this->once())
            ->method('fileLock')
            ->with($this->anything(), LOCK_EX)
            ->willReturn(true);

        $this->fileStorage->save(json_encode($data), $context);
        fclose($resource);
    }

    /**
     * Test save() merges new hashes with hashes already on disk.
     */
    public function testSaveMergesExistingHashesWithNewHashes(): void
    {
        $context = 'frontend';
        $existingHashes = ['js/existing.js' => 'sha256-OLD'];
        $newHashes      = ['js/new.js'      => 'sha256-NEW'];
        $merged         = array_merge($existingHashes, $newHashes);
        $existingJson   = json_encode($existingHashes);

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $driver->method('stat')->willReturn(['size' => strlen($existingJson)]);
        $driver->method('fileRead')->willReturn($existingJson);

        $staticDir = $this->buildWriteDirMock($driver);
        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);

        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $writtenContent = null;
        $driver->expects($this->once())
            ->method('fileWrite')
            ->willReturnCallback(function () use (&$writtenContent) {
                $args = func_get_args();
                $writtenContent = $args[1];
                return strlen($args[1]);
            });

        $result = $this->fileStorage->save(json_encode($newHashes), $context);

        $this->assertTrue($result);
        $this->assertNotNull($writtenContent);
        $this->assertEquals($merged, json_decode($writtenContent, true));

        fclose($resource);
    }

    /**
     * Test save() does not call fileWrite() when the incoming hashes are identical
     * to what is already on disk — avoids unnecessary I/O and mtime changes.
     */
    public function testSaveDoesNotWriteWhenHashesUnchanged(): void
    {
        $context = 'frontend';
        $hashes     = ['js/app.js' => 'sha256-ABC'];
        $serialized = json_encode($hashes);

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $driver->method('stat')->willReturn(['size' => strlen($serialized)]);
        $driver->method('fileRead')->willReturn($serialized);

        $staticDir = $this->buildWriteDirMock($driver);
        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);

        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $driver->expects($this->never())->method('fileWrite');

        $result = $this->fileStorage->save(json_encode($hashes), $context);
        $this->assertTrue($result);

        fclose($resource);
    }

    /**
     * Test save() always releases the lock and closes the handle (finally block),
     * even when the write completes successfully.
     */
    public function testSaveReleasesLockAfterSuccessfulWrite(): void
    {
        $context = 'frontend';
        $data    = ['js/app.js' => 'sha256-ABC'];

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $staticDir = $this->buildWriteDirMock($driver);

        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);
        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $driver->expects($this->once())->method('fileUnlock');
        $driver->expects($this->once())->method('fileClose');

        $this->fileStorage->save(json_encode($data), $context);
        fclose($resource);
    }

    /**
     * Test save() still releases the lock and closes the handle when
     * an unexpected exception escapes the read-modify-write cycle (finally block).
     */
    public function testSaveReleasesLockWhenSerializerThrows(): void
    {
        $context      = 'frontend';
        $incomingData = ['js/app.js' => 'sha256-NEW'];
        $existingJson = '{"js/old.js":"sha256-OLD"}';

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $driver->method('stat')->willReturn(['size' => strlen($existingJson)]);
        $driver->method('fileRead')->willReturn($existingJson);

        $staticDir = $this->buildWriteDirMock($driver);
        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);

        // First unserialize call (incoming data in save()) succeeds.
        // Second call (existing file content in saveHashesToFile()) throws an unexpected
        // RuntimeException — InvalidArgumentException (corrupt JSON) is now caught and
        // handled gracefully inside saveHashesToFile(), so only unexpected errors propagate.
        $callCount = 0;
        $this->serializer->method('unserialize')
            ->willReturnCallback(function () use (&$callCount, $incomingData) {
                $callCount++;
                if ($callCount === 1) {
                    return $incomingData; // parse incoming payload — succeeds
                }
                throw new \RuntimeException('Unexpected serializer failure'); // unexpected error — propagates
            });

        // Lock and close must be called even though an exception is thrown.
        $driver->expects($this->once())->method('fileUnlock');
        $driver->expects($this->once())->method('fileClose');

        $result = $this->fileStorage->save(json_encode($incomingData), $context);
        $this->assertFalse($result, 'save() should return false on unexpected exception');

        fclose($resource);
    }

    /**
     * Test save() returns false and fileClose/fileUnlock are still called when fileLock throws.
     *
     * fileLock throws after fileOpen succeeds, so $resource is set and the finally block
     * must still run to close the handle.
     */
    public function testSaveReturnsFalseAndClosesHandleWhenFileLockThrows(): void
    {
        $context = 'frontend';
        $data    = ['js/app.js' => 'sha256-ABC'];

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $staticDir = $this->buildWriteDirMock($driver);

        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);
        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);

        $driver->expects($this->once())
            ->method('fileLock')
            ->willThrowException(new \Magento\Framework\Exception\FileSystemException(__('Lock failed')));

        $driver->expects($this->once())->method('fileUnlock');
        $driver->expects($this->once())->method('fileClose');

        $result = $this->fileStorage->save(json_encode($data), $context);
        $this->assertFalse($result, 'save() should return false when fileLock throws');

        fclose($resource);
    }

    /**
     * Test save() overwrites an existing hash for the same key (array_merge last-write-wins).
     *
     * Protects against accidentally reversing the array_merge argument order,
     * which would silently break hash update semantics.
     */
    public function testSaveOverwritesExistingHashForSameKey(): void
    {
        $context      = 'frontend';
        $existingHash = ['js/app.js' => 'sha256-OLD'];
        $newHash      = ['js/app.js' => 'sha256-NEW'];
        $existingJson = json_encode($existingHash);

        $resource = fopen('php://memory', 'c+');
        $driver = $this->buildDriverMock($resource);
        $driver->method('stat')->willReturn(['size' => strlen($existingJson)]);
        $driver->method('fileRead')->willReturn($existingJson);

        $staticDir = $this->buildWriteDirMock($driver);
        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);

        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $writtenContent = null;
        $driver->expects($this->once())
            ->method('fileWrite')
            ->willReturnCallback(function () use (&$writtenContent) {
                $args = func_get_args();
                $writtenContent = $args[1];
                return strlen($args[1]);
            });

        $result = $this->fileStorage->save(json_encode($newHash), $context);

        $this->assertTrue($result);
        $decoded = json_decode($writtenContent, true);
        $this->assertEquals('sha256-NEW', $decoded['js/app.js'], 'New hash must overwrite existing hash for same key');

        fclose($resource);
    }

    /**
     * Test save() logs a warning and does NOT write when ftruncate() fails.
     *
     * ftruncate() is a raw PHP call on the native handle. Opening the handle
     * in read-only mode (php://temp, 'r') makes ftruncate() return false,
     * exercising the graceful-failure branch.
     */
    public function testSaveLogsWarningWhenFtruncateFails(): void
    {
        $context = 'frontend';
        $data    = ['js/app.js' => 'sha256-ABC'];

        // A read-only stream causes ftruncate() to return false.
        $readOnlyResource = fopen('php://temp', 'r');

        $driver = $this->buildDriverMock($readOnlyResource);
        // stat size=0 → existingHashes=[] → allHashes=data → they differ → ftruncate is reached
        $driver->method('stat')->willReturn(['size' => 0]);

        $staticDir = $this->buildWriteDirMock($driver);
        $this->filesystem->method('getDirectoryWrite')->willReturn($staticDir);

        $this->serializer->method('unserialize')
            ->willReturnCallback(fn($d) => json_decode($d, true) ?? []);
        $this->serializer->method('serialize')
            ->willReturnCallback(fn($d) => json_encode($d));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Failed to truncate'));

        $driver->expects($this->never())->method('fileWrite');
        $driver->expects($this->once())->method('fileUnlock');
        $driver->expects($this->once())->method('fileClose');

        $result = $this->fileStorage->save(json_encode($data), $context);
        $this->assertFalse($result, 'save() should return false when ftruncate fails');

        fclose($readOnlyResource);
    }
}
