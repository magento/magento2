<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity\Storage;

use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for File storage implementation.
 *
 * Tests all public methods: load(), save(), remove()
 *
 * @magentoAppIsolation enabled
 */
class FileTest extends TestCase
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
     * @var FileDriver
     */
    private FileDriver $fileDriver;

    /**
     * @var File
     */
    private File $storage;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var array
     */
    private array $testFiles = [];

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
        $this->fileDriver = $objectManager->get(FileDriver::class);
        $this->storage = $objectManager->get(File::class);
        $this->serializer = $objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        // Restore permissions for any files we modified
        foreach ($this->testFiles as $file) {
            if ($this->fileDriver->isExists($file)) {
                try {
                    $this->fileDriver->changePermissions($file, 0644);
                    $this->fileDriver->deleteFile($file);
                } catch (\Exception $e) {
                    // Ignore cleanup errors
                }
            }
        }

        // Clean up test directories
        foreach ($this->testDirs as $dir) {
            if ($this->staticDir->isExist($dir)) {
                try {
                    $this->staticDir->delete($dir);
                } catch (\Exception $e) {
                    // Ignore cleanup errors
                }
            }
        }

        $this->testFiles = [];
        $this->testDirs = [];
    }

    /**
     * Test load() returns serialized data when file exists and is readable.
     *
     * @return void
     */
    public function testLoadReturnsSerializedDataWhenFileExists(): void
    {
        $testContext = 'test/context/load-success';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $testData = [
            'js/app.js' => 'sha256-ABC123',
            'js/vendor.js' => 'sha256-XYZ789'
        ];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        $result = $this->storage->load($testContext);

        $this->assertNotNull($result);
        $this->assertJson($result);
        $decoded = $this->serializer->unserialize($result);
        $this->assertEquals($testData, $decoded);
    }

    /**
     * Test load() returns null when file does not exist.
     *
     * @return void
     */
    public function testLoadReturnsNullWhenFileDoesNotExist(): void
    {
        $result = $this->storage->load('nonexistent/context/path');
        $this->assertNull($result);
    }

    /**
     * Test load() returns null when file is unreadable (chmod 000).
     *
     * @return void
     */
    public function testLoadReturnsNullWhenFileIsUnreadable(): void
    {
        $testContext = 'test/context/unreadable';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $testData = ['js/test.js' => 'sha256-ABC123'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        $fullPath = $this->staticDir->getAbsolutePath($testFile);
        $this->testFiles[] = $fullPath;

        // Verify file is readable initially
        $result = $this->storage->load($testContext);
        $this->assertNotNull($result);

        // Make file unreadable
        $this->fileDriver->changePermissions($fullPath, 0000);

        // Verify graceful degradation - should return null, not throw exception
        $result = $this->storage->load($testContext);
        $this->assertNull($result, 'Storage should return null when file is unreadable');
    }

    /**
     * Test load() merges individual and merged file hashes.
     *
     * @return void
     */
    public function testLoadMergesIndividualAndMergedHashes(): void
    {
        $testContext = 'test/context/merged';
        $individualFile = $testContext . '/sri-hashes.json';
        $mergedFile = '_cache/merged/sri-hashes.json';
        $this->testDirs[] = $testContext;
        $this->testDirs[] = '_cache/merged';

        $individualData = ['js/app.js' => 'sha256-ABC123'];
        $mergedData = ['_cache/merged/bundle.js' => 'sha256-MERGED456'];

        $this->staticDir->writeFile($individualFile, $this->serializer->serialize($individualData));
        $this->staticDir->writeFile($mergedFile, $this->serializer->serialize($mergedData));

        $result = $this->storage->load($testContext);

        $this->assertNotNull($result);
        $decoded = $this->serializer->unserialize($result);
        $this->assertArrayHasKey('js/app.js', $decoded);
        $this->assertArrayHasKey('_cache/merged/bundle.js', $decoded);
        $this->assertEquals('sha256-ABC123', $decoded['js/app.js']);
        $this->assertEquals('sha256-MERGED456', $decoded['_cache/merged/bundle.js']);
    }

    /**
     * Test load() with null context.
     *
     * @return void
     */
    public function testLoadWithNullContext(): void
    {
        $testFile = 'sri-hashes.json';
        $this->testDirs[] = '';

        $testData = ['js/base.js' => 'sha256-BASE123'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        $result = $this->storage->load(null);

        $this->assertNotNull($result);
        $decoded = $this->serializer->unserialize($result);
        $this->assertEquals($testData, $decoded);
    }

    /**
     * Test save() creates file with correct data.
     *
     * @return void
     */
    public function testSaveCreatesFileWithCorrectData(): void
    {
        $testContext = 'test/context/save-new';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $testData = ['js/new.js' => 'sha256-NEW123'];
        $serializedData = $this->serializer->serialize($testData);

        $result = $this->storage->save($serializedData, $testContext);

        $this->assertTrue($result);
        $this->assertTrue($this->staticDir->isFile($testFile));

        $savedContent = $this->staticDir->readFile($testFile);
        $decoded = $this->serializer->unserialize($savedContent);
        $this->assertEquals($testData, $decoded);
    }

    /**
     * Test save() merges with existing data.
     *
     * @return void
     */
    public function testSaveMergesWithExistingData(): void
    {
        $testContext = 'test/context/save-merge';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        // Create initial data
        $existingData = ['js/existing.js' => 'sha256-EXISTING'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($existingData));

        // Save new data
        $newData = ['js/new.js' => 'sha256-NEW'];
        $result = $this->storage->save($this->serializer->serialize($newData), $testContext);

        $this->assertTrue($result);

        // Verify merged data
        $savedContent = $this->staticDir->readFile($testFile);
        $decoded = $this->serializer->unserialize($savedContent);
        $this->assertArrayHasKey('js/existing.js', $decoded);
        $this->assertArrayHasKey('js/new.js', $decoded);
        $this->assertEquals('sha256-EXISTING', $decoded['js/existing.js']);
        $this->assertEquals('sha256-NEW', $decoded['js/new.js']);
    }

    /**
     * Test save() separates merged and individual files.
     *
     * @return void
     */
    public function testSaveSeparatesMergedAndIndividualFiles(): void
    {
        $testContext = 'test/context/save-separate';
        $individualFile = $testContext . '/sri-hashes.json';
        $mergedFile = '_cache/merged/sri-hashes.json';
        $this->testDirs[] = $testContext;
        $this->testDirs[] = '_cache/merged';

        $testData = [
            'js/individual.js' => 'sha256-INDIVIDUAL',
            '_cache/merged/bundle.js' => 'sha256-MERGED'
        ];

        $result = $this->storage->save($this->serializer->serialize($testData), $testContext);

        $this->assertTrue($result);
        $this->assertTrue($this->staticDir->isFile($individualFile));
        $this->assertTrue($this->staticDir->isFile($mergedFile));

        $individualContent = $this->serializer->unserialize($this->staticDir->readFile($individualFile));
        $this->assertArrayHasKey('js/individual.js', $individualContent);
        $this->assertArrayNotHasKey('_cache/merged/bundle.js', $individualContent);

        $mergedContent = $this->serializer->unserialize($this->staticDir->readFile($mergedFile));
        $this->assertArrayHasKey('_cache/merged/bundle.js', $mergedContent);
        $this->assertArrayNotHasKey('js/individual.js', $mergedContent);
    }

    /**
     * Test save() returns false for invalid JSON data.
     *
     * @return void
     */
    public function testSaveReturnsFalseForInvalidData(): void
    {
        $result = $this->storage->save('INVALID JSON {{{', 'test/context');
        $this->assertFalse($result);
    }

    /**
     * Test save() returns false for non-array data.
     *
     * @return void
     */
    public function testSaveReturnsFalseForNonArrayData(): void
    {
        $result = $this->storage->save($this->serializer->serialize('string value'), 'test/context');
        $this->assertFalse($result);
    }

    /**
     * Test save() with null context.
     *
     * @return void
     */
    public function testSaveWithNullContext(): void
    {
        $testFile = 'sri-hashes.json';
        $this->testDirs[] = '';

        $testData = ['js/base.js' => 'sha256-BASE'];
        $result = $this->storage->save($this->serializer->serialize($testData), null);

        $this->assertTrue($result);
        $this->assertTrue($this->staticDir->isFile($testFile));
    }

    /**
     * Test remove() deletes existing file.
     *
     * @return void
     */
    public function testRemoveDeletesExistingFile(): void
    {
        $testContext = 'test/context/remove';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $testData = ['js/remove.js' => 'sha256-REMOVE'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        $this->assertTrue($this->staticDir->isFile($testFile));

        $result = $this->storage->remove($testContext);

        $this->assertTrue($result);
        $this->assertFalse($this->staticDir->isFile($testFile));
    }

    /**
     * Test remove() returns true when file does not exist.
     *
     * @return void
     */
    public function testRemoveReturnsTrueWhenFileDoesNotExist(): void
    {
        $result = $this->storage->remove('nonexistent/context');
        $this->assertTrue($result);
    }

    /**
     * Test remove() with null context.
     *
     * @return void
     */
    public function testRemoveWithNullContext(): void
    {
        $testFile = 'sri-hashes.json';
        $this->testDirs[] = '';

        $testData = ['js/base.js' => 'sha256-BASE'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        $this->assertTrue($this->staticDir->isFile($testFile));

        $result = $this->storage->remove(null);

        $this->assertTrue($result);
        $this->assertFalse($this->staticDir->isFile($testFile));
    }

    /**
     * Test repository integration when file is unreadable.
     *
     * @return void
     */
    public function testRepositoryReturnsEmptyArrayWhenFileIsUnreadable(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $testContext = 'test/context/repo-unreadable';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $testData = ['js/test.js' => 'sha256-ABC123'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        $fullPath = $this->staticDir->getAbsolutePath($testFile);
        $this->testFiles[] = $fullPath;

        // Create repository and verify data is returned initially
        $repository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => $testContext]
        );
        $this->assertNotEmpty($repository->getAll());

        // Make file unreadable
        $this->fileDriver->changePermissions($fullPath, 0000);

        // Create new repository instance (to clear cached data)
        $repository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => $testContext]
        );

        // Verify graceful degradation
        $result = $repository->getAll();
        $this->assertIsArray($result);
        $this->assertEmpty($result, 'Repository should return empty array when file is unreadable');
        $this->assertNull($repository->getByPath('js/test.js'));
    }

    // ========================================================================
    // EXCEPTION PATH TESTS - Verify exception handling returns proper values
    // ========================================================================

    /**
     * Test save() catches InvalidArgumentException from serializer and returns false.
     *
     * @return void
     */
    public function testSaveCatchesInvalidArgumentExceptionFromSerializer(): void
    {
        // Pass invalid JSON string that will cause serializer to throw InvalidArgumentException
        $result = $this->storage->save('invalid json {{{', 'test/context');

        $this->assertFalse($result, 'save() should catch InvalidArgumentException and return false');
    }

    /**
     * Test load() and save() handle an empty (zero-byte) storage file gracefully.
     *
     * Simulates a file left empty after a truncate-then-crash scenario.
     *
     * @return void
     */
    public function testEmptyFileIsHandledGracefully(): void
    {
        $testContext = 'test/context/empty-file';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $this->staticDir->writeFile($testFile, '');

        $loadResult = $this->storage->load($testContext);
        $this->assertNull($loadResult, 'load() should return null for an empty file');

        $newData = ['js/recovered.js' => 'sha256-RECOVERED'];
        $saveResult = $this->storage->save($this->serializer->serialize($newData), $testContext);
        $this->assertTrue($saveResult, 'save() should succeed and repopulate an empty file');

        $savedContent = $this->staticDir->readFile($testFile);
        $this->assertJson($savedContent);
        $decoded = $this->serializer->unserialize($savedContent);
        $this->assertArrayHasKey('js/recovered.js', $decoded);
    }

    /**
     * Test save() catches FileSystemException when writeFile fails and returns false.
     *
     * @return void
     */
    public function testSaveCatchesFileSystemExceptionAndReturnsFalse(): void
    {
        $testContext = 'test/context/save-exception';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        // Create file with initial data
        $existingData = ['js/existing.js' => 'sha256-EXISTING'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($existingData));

        // Make file read-only to cause FileSystemException during write
        $filePath = $this->staticDir->getAbsolutePath($testFile);
        $this->testFiles[] = $filePath;
        $this->fileDriver->changePermissions($filePath, 0444);

        // Try to save - should catch FileSystemException and return false
        $newData = ['js/new.js' => 'sha256-NEW'];
        $result = $this->storage->save($this->serializer->serialize($newData), $testContext);

        $this->assertFalse($result, 'save() should catch FileSystemException and return false');

        // Restore permissions for cleanup
        $this->fileDriver->changePermissions($filePath, 0644);
    }

    /**
     * Test remove() catches FileSystemException and returns false.
     *
     * @return void
     */
    public function testRemoveCatchesFileSystemExceptionAndReturnsFalse(): void
    {
        $testContext = 'test/context/remove-exception';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        // Create file
        $testData = ['js/test.js' => 'sha256-TEST'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        // Make file immutable by removing all write permissions
        // This will cause delete to fail with FileSystemException
        $filePath = $this->staticDir->getAbsolutePath($testFile);
        $this->testFiles[] = $filePath;

        // Make parent directory read-only to prevent deletion
        $dirPath = $this->staticDir->getAbsolutePath($testContext);
        $this->fileDriver->changePermissions($dirPath, 0555);

        // Try to remove - should catch FileSystemException and return false
        $result = $this->storage->remove($testContext);

        // Restore permissions for cleanup
        $this->fileDriver->changePermissions($dirPath, 0755);

        $this->assertFalse($result, 'remove() should catch FileSystemException and return false');
    }

    /**
     * Test save() creates a new file correctly when the file does not exist yet.
     *
     * @return void
     */
    public function testSaveCreatesNewFileWhenFileDoesNotExist(): void
    {
        $testContext = 'test/context/new-file';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $this->assertFalse($this->staticDir->isFile($testFile));

        $data = ['js/new.js' => 'sha256-NEW'];
        $result = $this->storage->save($this->serializer->serialize($data), $testContext);

        $this->assertTrue($result);
        $this->assertTrue($this->staticDir->isFile($testFile));
        $decoded = $this->serializer->unserialize($this->staticDir->readFile($testFile));
        $this->assertEquals($data, $decoded);
    }

    /**
     * Test save() overwrites a corrupted existing file and returns true.
     *
     * InvalidArgumentException from deserializing a corrupt file is caught
     * inside saveHashesToFile(), which logs a warning and falls back to an
     * empty array so the new hashes are written cleanly.
     *
     * @return void
     */
    public function testSaveHandlesCorruptedJsonInExistingFile(): void
    {
        $testContext = 'test/context/corrupt-json';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $this->staticDir->writeFile($testFile, '{broken json [[[}}}');

        $newData = ['js/new.js' => 'sha256-NEW'];
        $result = $this->storage->save($this->serializer->serialize($newData), $testContext);

        $this->assertTrue($result, 'save() should succeed and overwrite a corrupted existing file');
        $savedContent = $this->staticDir->readFile($testFile);
        $this->assertJson($savedContent, 'Overwritten file should contain valid JSON');
        $decoded = json_decode($savedContent, true);
        $this->assertArrayHasKey('js/new.js', $decoded, 'New hash should be present after overwrite');
    }

    /**
     * Test save() does not write to disk when hashes are unchanged.
     *
     * @return void
     */
    public function testSaveDoesNotWriteWhenHashesUnchanged(): void
    {
        $testContext = 'test/context/no-change';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $data = ['js/app.js' => 'sha256-ABC'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($data));

        $mtimeBefore = filemtime($this->staticDir->getAbsolutePath($testFile));
        sleep(1);

        $this->storage->save($this->serializer->serialize($data), $testContext);

        $mtimeAfter = filemtime($this->staticDir->getAbsolutePath($testFile));
        $this->assertEquals($mtimeBefore, $mtimeAfter, 'File should not be rewritten when hashes are unchanged');
    }

    /**
     * Test load() returns null when file is unreadable (FileSystemException path).
     *
     * @return void
     * @throws FileSystemException
     */
    public function testLoadReturnsNullWhenFileIsUnreadableExceptionPath(): void
    {
        $testContext = 'test/context/load-exception';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        // Create file
        $testData = ['js/test.js' => 'sha256-TEST'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($testData));

        // Make file unreadable (chmod 000) to trigger exception in loadHashesFromFile
        $filePath = $this->staticDir->getAbsolutePath($testFile);
        $this->testFiles[] = $filePath;
        $this->fileDriver->changePermissions($filePath, 0000);

        // Should handle the exception gracefully and return null
        $result = $this->storage->load($testContext);

        $this->assertNull($result, 'load() should return null when file read causes exception');

        // Restore permissions for cleanup
        $this->fileDriver->changePermissions($filePath, 0644);
    }

    /**
     * Test load() handles InvalidArgumentException from JSON parsing gracefully.
     *
     * @return void
     */
    public function testLoadHandlesInvalidJsonException(): void
    {
        $testContext = 'test/context/invalid-json-exception';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        // Create file with invalid JSON that will cause serializer to throw
        $this->staticDir->writeFile($testFile, '{broken json [[[}}}');

        // Should catch InvalidArgumentException in loadHashesFromFile and return null
        $result = $this->storage->load($testContext);

        $this->assertNull($result, 'load() should return null when JSON parsing throws InvalidArgumentException');
    }

    // ========================================================================
    // DIRECTORY CREATION TEST
    // ========================================================================

    /**
     * Test that save() creates the directory if it does not exist.
     *
     * @return void
     */
    public function testSaveCreatesDirectoryIfNotExists(): void
    {
        $testContext = 'test/context/deeply/nested/new/dir';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = 'test/context/deeply';

        $this->assertFalse($this->staticDir->isExist($testContext));

        $data = ['js/app.js' => 'sha256-ABC'];
        $result = $this->storage->save($this->serializer->serialize($data), $testContext);

        $this->assertTrue($result);
        $this->assertTrue($this->staticDir->isFile($testFile));
    }

    // ========================================================================
    // MULTIPLE SEQUENTIAL SAVES TEST
    // ========================================================================

    /**
     * Test that multiple sequential saves accumulate all hashes correctly.
     *
     * @return void
     */
    public function testMultipleSequentialSavesAccumulateHashes(): void
    {
        $testContext = 'test/context/sequential';
        $this->testDirs[] = $testContext;

        $this->storage->save($this->serializer->serialize(['js/first.js' => 'sha256-FIRST']), $testContext);
        $this->storage->save($this->serializer->serialize(['js/second.js' => 'sha256-SECOND']), $testContext);
        $this->storage->save($this->serializer->serialize(['js/third.js' => 'sha256-THIRD']), $testContext);

        $result = $this->storage->load($testContext);
        $this->assertNotNull($result);

        $decoded = $this->serializer->unserialize($result);
        $this->assertArrayHasKey('js/first.js', $decoded);
        $this->assertArrayHasKey('js/second.js', $decoded);
        $this->assertArrayHasKey('js/third.js', $decoded);
    }

    // ========================================================================
    // isMergedFilePath() TESTS
    // ========================================================================

    /**
     * Test that paths containing _cache/merged/ are routed to the merged storage file.
     *
     * @return void
     */
    public function testMergedFilePathRoutesToMergedStorage(): void
    {
        $mergedFile = '_cache/merged/sri-hashes.json';
        $this->testDirs[] = '_cache/merged';

        $data = ['_cache/merged/bundle.js' => 'sha256-MERGED'];
        $this->storage->save($this->serializer->serialize($data), null);

        $this->assertTrue(
            $this->staticDir->isFile($mergedFile),
            'Merged path should be written to _cache/merged/sri-hashes.json'
        );

        $content = $this->serializer->unserialize($this->staticDir->readFile($mergedFile));
        $this->assertArrayHasKey('_cache/merged/bundle.js', $content);
    }

    /**
     * Test that paths not containing _cache/merged/ are routed to the per-context storage file.
     *
     * @return void
     */
    public function testNonMergedFilePathRoutesToIndividualStorage(): void
    {
        $testContext = 'test/context/routing';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $data = ['js/app.js' => 'sha256-INDIVIDUAL'];
        $this->storage->save($this->serializer->serialize($data), $testContext);

        $this->assertTrue(
            $this->staticDir->isFile($testFile),
            'Non-merged path should be written to per-context sri-hashes.json'
        );
        $this->assertFalse(
            $this->staticDir->isFile('_cache/merged/sri-hashes.json'),
            'Non-merged path should not write to merged storage'
        );
    }

    // ========================================================================
    // load() EDGE CASE TESTS
    // ========================================================================

    /**
     * Test that load() returns merged hashes when only the merged file exists.
     *
     * @return void
     */
    public function testLoadReturnsMergedHashesWhenOnlyMergedFileExists(): void
    {
        $testContext = 'test/context/merged-only';
        $mergedFile = '_cache/merged/sri-hashes.json';
        $this->testDirs[] = $testContext;
        $this->testDirs[] = '_cache/merged';

        $mergedData = ['_cache/merged/bundle.js' => 'sha256-MERGED'];
        $this->staticDir->writeFile($mergedFile, $this->serializer->serialize($mergedData));

        $result = $this->storage->load($testContext);

        $this->assertNotNull($result);
        $decoded = $this->serializer->unserialize($result);
        $this->assertArrayHasKey('_cache/merged/bundle.js', $decoded);
    }

    /**
     * Test that load() returns individual hashes when only the individual file exists.
     *
     * @return void
     */
    public function testLoadReturnsIndividualHashesWhenOnlyIndividualFileExists(): void
    {
        $testContext = 'test/context/individual-only';
        $testFile = $testContext . '/sri-hashes.json';
        $this->testDirs[] = $testContext;

        $individualData = ['js/app.js' => 'sha256-INDIVIDUAL'];
        $this->staticDir->writeFile($testFile, $this->serializer->serialize($individualData));

        $result = $this->storage->load($testContext);

        $this->assertNotNull($result);
        $decoded = $this->serializer->unserialize($result);
        $this->assertArrayHasKey('js/app.js', $decoded);
    }
}
