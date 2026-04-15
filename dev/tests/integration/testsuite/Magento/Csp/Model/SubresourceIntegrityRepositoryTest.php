<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Model\SubresourceIntegrity\StorageInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration tests for SubresourceIntegrityRepository
 *
 * Tests the repository that manages SRI hash storage and retrieval for a specific context.
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubresourceIntegrityRepositoryTest extends TestCase
{
    /**
     * Name of the SRI hash storage file
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * Test context path for SRI hash storage (area/theme/locale)
     */
    private const TEST_CONTEXT = 'frontend/Magento/luma/en_US';

    /**
     * Test asset path for hash storage
     */
    private const TEST_PATH = 'js/test-file.js';

    /**
     * Test hash value in SHA-256 format
     */
    private const TEST_HASH = 'sha256-testHash123';

    /**
     * Repository instance under test
     *
     * @var SubresourceIntegrityRepository
     */
    private SubresourceIntegrityRepository $repository;

    /**
     * Static directory writer for file operations
     *
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * JSON serializer for hash file operations
     *
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * List of file paths to cleanup after each test
     *
     * @var array
     */
    private array $filesToCleanup = [];

    /**
     * Previous application mode (saved for restoration in tearDown)
     *
     * @var string
     */
    private string $prevMode;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->prevMode = $objectManager->get(State::class)->getMode();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        $this->serializer = $objectManager->get(SerializerInterface::class);

        $filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        // Create fresh repository instance for each test
        $this->repository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => self::TEST_CONTEXT]
        );

        $this->filesToCleanup = [];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ($this->filesToCleanup as $file) {
            if ($this->staticDir->isExist($file)) {
                $this->staticDir->delete($file);
            }
        }

        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode($this->prevMode);

        parent::tearDown();
    }

    /**
     * @magentoAppArea frontend
     */
    public function testSaveAndRetrieveHash(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();
        $integrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => self::TEST_PATH, 'hash' => self::TEST_HASH]]
        );

        $saveResult = $this->repository->save($integrity);
        $this->assertTrue($saveResult, 'Save should succeed');

        $retrieved = $this->repository->getByPath(self::TEST_PATH);
        $this->assertInstanceOf(SubresourceIntegrity::class, $retrieved);
        $this->assertEquals(self::TEST_PATH, $retrieved->getPath());
        $this->assertEquals(self::TEST_HASH, $retrieved->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testSaveBunchMultipleHashes(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();
        $bunch = [];

        // Create 100 integrity objects
        for ($i = 0; $i < 100; $i++) {
            $bunch[] = $objectManager->create(
                SubresourceIntegrity::class,
                [
                    'data' => [
                        'path' => "js/file{$i}.js",
                        'hash' => "sha256-hash{$i}"
                    ]
                ]
            );
        }

        $saveResult = $this->repository->saveBunch($bunch);
        $this->assertTrue($saveResult, 'SaveBunch should succeed');

        // Verify all hashes retrievable
        $all = $this->repository->getAll();
        $this->assertCount(100, $all);

        // Verify random samples
        $this->assertEquals('sha256-hash0', $this->repository->getByPath('js/file0.js')->getHash());
        $this->assertEquals('sha256-hash50', $this->repository->getByPath('js/file50.js')->getHash());
        $this->assertEquals('sha256-hash99', $this->repository->getByPath('js/file99.js')->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGetAllReturnsArray(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();

        // Save multiple hashes
        $hashes = [
            'js/file1.js' => 'sha256-hash1',
            'js/file2.js' => 'sha256-hash2',
            'js/file3.js' => 'sha256-hash3',
        ];

        $bunch = [];
        foreach ($hashes as $path => $hash) {
            $bunch[] = $objectManager->create(
                SubresourceIntegrity::class,
                ['data' => ['path' => $path, 'hash' => $hash]]
            );
        }

        $this->repository->saveBunch($bunch);

        $all = $this->repository->getAll();
        $this->assertIsArray($all);
        $this->assertCount(3, $all);

        foreach ($all as $integrity) {
            $this->assertInstanceOf(SubresourceIntegrity::class, $integrity);
        }
    }

    /**
     * @magentoAppArea frontend
     */
    public function testDeleteAllRemovesData(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();
        $integrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => self::TEST_PATH, 'hash' => self::TEST_HASH]]
        );

        $this->repository->save($integrity);

        // Verify file exists
        $filePath = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;
        $this->assertTrue($this->staticDir->isExist($filePath), 'File should exist after save');

        $deleteResult = $this->repository->deleteAll();
        $this->assertTrue($deleteResult, 'DeleteAll should succeed');

        // Verify data removed
        $all = $this->repository->getAll();
        $this->assertEmpty($all, 'Data should be empty after deleteAll');

        // Verify file deleted
        $this->assertFalse($this->staticDir->isExist($filePath), 'File should be deleted');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testLoadFromExistingFile(): void
    {
        $filePath = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        // Manually create sri-hashes.json
        if (!$this->staticDir->isExist(self::TEST_CONTEXT)) {
            $this->staticDir->create(self::TEST_CONTEXT);
        }

        $data = [
            'js/existing1.js' => 'sha256-existing1',
            'js/existing2.js' => 'sha256-existing2',
        ];
        $this->staticDir->writeFile($filePath, $this->serializer->serialize($data));

        // Create new repository instance (should load existing file)
        $objectManager = Bootstrap::getObjectManager();
        $newRepository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => self::TEST_CONTEXT]
        );

        $retrieved = $newRepository->getByPath('js/existing1.js');
        $this->assertInstanceOf(SubresourceIntegrity::class, $retrieved);
        $this->assertEquals('sha256-existing1', $retrieved->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulHandlingCorruptedJson(): void
    {
        $filePath = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        // Write invalid JSON
        if (!$this->staticDir->isExist(self::TEST_CONTEXT)) {
            $this->staticDir->create(self::TEST_CONTEXT);
        }
        $this->staticDir->writeFile($filePath, '{invalid json {{');

        // Create repository (should handle gracefully)
        $objectManager = Bootstrap::getObjectManager();
        $repository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => self::TEST_CONTEXT]
        );

        $all = $repository->getAll();
        $this->assertIsArray($all);
        $this->assertEmpty($all, 'Should return empty array for corrupted JSON');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulHandlingNonArrayData(): void
    {
        $filePath = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        // Write valid JSON but not an array/object
        if (!$this->staticDir->isExist(self::TEST_CONTEXT)) {
            $this->staticDir->create(self::TEST_CONTEXT);
        }
        $this->staticDir->writeFile($filePath, '"hello"');

        $objectManager = Bootstrap::getObjectManager();
        $repository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => self::TEST_CONTEXT]
        );

        $all = $repository->getAll();
        $this->assertIsArray($all);
        $this->assertEmpty($all, 'Should return empty array for non-array data');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testContextIsolation(): void
    {
        $contextA = 'frontend/Magento/luma/en_US';
        $contextB = 'frontend/Magento/luma/de_DE';

        $this->filesToCleanup[] = $contextA . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $contextB . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();

        // Repository A
        $repositoryA = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => $contextA]
        );

        // Repository B
        $repositoryB = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => $contextB]
        );

        // Save to A
        $integrityA = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/test.js', 'hash' => 'sha256-hashA']]
        );
        $repositoryA->save($integrityA);

        // Retrieve from B
        $retrievedB = $repositoryB->getByPath('js/test.js');
        $this->assertNull($retrievedB, 'Context B should not see Context A data');

        // Save to B
        $integrityB = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/test.js', 'hash' => 'sha256-hashB']]
        );
        $repositoryB->save($integrityB);

        // Verify A still has its own data
        $retrievedA = $repositoryA->getByPath('js/test.js');
        $this->assertEquals('sha256-hashA', $retrievedA->getHash());

        // Verify B has its own data
        $retrievedB = $repositoryB->getByPath('js/test.js');
        $this->assertEquals('sha256-hashB', $retrievedB->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testDataObjectTransformation(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();
        $integrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => self::TEST_PATH, 'hash' => self::TEST_HASH]]
        );

        $this->repository->save($integrity);

        // Retrieve via getByPath
        $retrieved = $this->repository->getByPath(self::TEST_PATH);

        // Assert it's a SubresourceIntegrity object (not array)
        $this->assertInstanceOf(SubresourceIntegrity::class, $retrieved);

        // Assert methods work
        $this->assertEquals(self::TEST_PATH, $retrieved->getPath());
        $this->assertEquals(self::TEST_HASH, $retrieved->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGetByPathReturnsNullForNonExistent(): void
    {
        $retrieved = $this->repository->getByPath('js/does-not-exist.js');
        $this->assertNull($retrieved, 'Should return null for non-existent path');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testUpdateExistingHash(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();

        // Save initial hash
        $integrity1 = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => self::TEST_PATH, 'hash' => 'sha256-original']]
        );
        $this->repository->save($integrity1);

        // Update with new hash
        $integrity2 = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => self::TEST_PATH, 'hash' => 'sha256-updated']]
        );
        $this->repository->save($integrity2);

        // Verify updated hash
        $retrieved = $this->repository->getByPath(self::TEST_PATH);
        $this->assertEquals('sha256-updated', $retrieved->getHash(), 'Hash should be updated');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testSaveBunchPreservesExistingData(): void
    {
        $this->filesToCleanup[] = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();

        // Save initial data
        $integrity1 = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/existing.js', 'hash' => 'sha256-existing']]
        );
        $this->repository->save($integrity1);

        // Save bunch with new data
        $bunch = [];
        for ($i = 0; $i < 5; $i++) {
            $bunch[] = $objectManager->create(
                SubresourceIntegrity::class,
                ['data' => ['path' => "js/new{$i}.js", 'hash' => "sha256-new{$i}"]]
            );
        }
        $this->repository->saveBunch($bunch);

        // Verify existing data still present
        $existing = $this->repository->getByPath('js/existing.js');
        $this->assertNotNull($existing);
        $this->assertEquals('sha256-existing', $existing->getHash());

        // Verify new data added
        $this->assertNotNull($this->repository->getByPath('js/new0.js'));
        $this->assertNotNull($this->repository->getByPath('js/new4.js'));

        // Total should be 6
        $this->assertCount(6, $this->repository->getAll());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testEmptyFileReturnsEmptyArray(): void
    {
        $filePath = self::TEST_CONTEXT . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        // Create empty JSON object
        if (!$this->staticDir->isExist(self::TEST_CONTEXT)) {
            $this->staticDir->create(self::TEST_CONTEXT);
        }
        $this->staticDir->writeFile($filePath, '{}');

        $objectManager = Bootstrap::getObjectManager();
        $repository = $objectManager->create(
            SubresourceIntegrityRepository::class,
            ['context' => self::TEST_CONTEXT]
        );

        $all = $repository->getAll();
        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }
}
