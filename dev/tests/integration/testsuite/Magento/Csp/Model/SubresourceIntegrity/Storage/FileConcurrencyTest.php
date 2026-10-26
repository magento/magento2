<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests file locking during concurrent SRI hash writes.
 *
 * @magentoAppIsolation enabled
 */
class FileConcurrencyTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * @var File
     */
    private File $storage;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var FileDriver
     */
    private FileDriver $fileDriver;

    /**
     * @var array
     */
    private array $testDirs = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->storage = $objectManager->get(File::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
        $this->fileDriver = $objectManager->get(FileDriver::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->testDirs as $dir) {
            if ($this->staticDir->isExist($dir)) {
                try {
                    $this->staticDir->delete($dir);
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }

        $this->testDirs = [];
    }

    /**
     * Test that concurrent calls to File::save() through the storage API preserve all hashes.
     *
     * Directly exercises saveHashesToFile() to verify the TOCTOU lock fix prevents data loss
     * under parallel SCD deployments (--jobs > 1).
     *
     * @return void
     */
    public function testConcurrentSavesThroughStorageApi(): void
    {
        $testContext = 'test/concurrent/storage-api';
        $this->testDirs[] = $testContext;

        $numWorkers = 5;
        $workerScript = __DIR__ . '/_scripts/concurrent_storage_save_worker.php';

        $this->assertFileExists($workerScript);

        $staticDirPath = $this->staticDir->getAbsolutePath('');

        $handles = [];
        for ($i = 0; $i < $numWorkers; $i++) {
            $cmd = sprintf(
                'php %s %s %s %d 2>&1',
                escapeshellarg($workerScript),
                escapeshellarg($testContext),
                escapeshellarg($staticDirPath),
                $i
            );
            $handles[$i] = popen($cmd, 'r'); // phpcs:ignore Magento2.Security.InsecureFunction.Found
            $this->assertIsResource($handles[$i], "Failed to start worker {$i}");
        }

        foreach ($handles as $i => $handle) {
            $exitCode = pclose($handle);
            $this->assertSame(0, $exitCode, "Worker {$i} exited with code {$exitCode}");
        }

        $result = $this->storage->load($testContext);
        $this->assertNotNull($result, 'Storage file should exist after concurrent saves');

        $allHashes = $this->serializer->unserialize($result);
        $this->assertCount($numWorkers, $allHashes, 'All hashes from all workers should be preserved');

        for ($i = 0; $i < $numWorkers; $i++) {
            $this->assertArrayHasKey("worker_{$i}.js", $allHashes);
            $this->assertEquals("sha256-WORKER{$i}", $allHashes["worker_{$i}.js"]);
        }
    }
}
