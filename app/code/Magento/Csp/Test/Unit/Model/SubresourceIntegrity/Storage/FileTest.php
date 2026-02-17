<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Model\SubresourceIntegrity\Storage;

use Magento\Csp\Model\SubresourceIntegrity\Storage\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
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
     * Test saving separates merged and individual files
     */
    public function testSaveSeparatesMergedAndIndividualFiles(): void
    {
        $context = 'frontend';
        $data = [
            'frontend/path/file.js' => 'sha256-individual',
            '_cache/merged/abc123.min.js' => 'sha256-merged',
            'frontend/another.js' => 'sha256-individual2'
        ];

        $staticDir = $this->createMock(WriteInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($data))
            ->willReturn($data);

        $staticDir->method('isFile')
            ->willReturn(false);

        $this->serializer->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode($data);
            });

        $writeFileCalls = [];
        $staticDir->method('writeFile')
            ->willReturnCallback(function ($path, $content) use (&$writeFileCalls) {
                $writeFileCalls[] = $path;
                return strlen($content);
            });

        $result = $this->fileStorage->save(json_encode($data), $context);

        $this->assertTrue($result);
        $this->assertContains('_cache/merged/sri-hashes.json', $writeFileCalls);
        $this->assertContains('frontend/sri-hashes.json', $writeFileCalls);
    }

    /**
     * Test saving only writes when content has changed
     */
    public function testSaveOnlyWritesWhenContentChanged(): void
    {
        $context = 'frontend';
        $data = ['frontend/file.js' => 'sha256-hash'];
        $existingData = ['frontend/file.js' => 'sha256-hash'];

        $staticDir = $this->createMock(WriteInterface::class);
        $this->filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($staticDir);

        $this->serializer->method('unserialize')
            ->willReturnCallback(function ($jsonData) {
                $decoded = json_decode($jsonData, true);
                return is_array($decoded) ? $decoded : [];
            });

        $staticDir->method('isFile')
            ->willReturnCallback(function ($path) {
                return $path === 'frontend/sri-hashes.json';
            });

        $staticDir->method('readFile')
            ->willReturnCallback(function ($path) use ($existingData) {
                if ($path === 'frontend/sri-hashes.json') {
                    return json_encode($existingData);
                }
                return '';
            });

        $this->serializer->method('serialize')
            ->willReturnCallback(function ($data) {
                return json_encode($data);
            });

        $staticDir->expects($this->never())
            ->method('writeFile');

        $result = $this->fileStorage->save(json_encode($data), $context);

        $this->assertTrue($result);
    }

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
            ->method('critical');

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
            ->with($this->stringContains('Invalid JSON in frontend/sri-hashes.json'));

        $result = $this->fileStorage->load($context);

        $this->assertNull($result);
    }
}
