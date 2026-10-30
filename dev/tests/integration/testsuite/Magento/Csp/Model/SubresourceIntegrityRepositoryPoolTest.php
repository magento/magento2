<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for SubresourceIntegrityRepositoryPool
 *
 * Tests the pool that manages multiple repository instances for different contexts.
 *
 * @magentoAppIsolation enabled
 */
class SubresourceIntegrityRepositoryPoolTest extends TestCase
{
    /**
     * Name of the SRI hash storage file
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $pool;

    /**
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * @var array
     */
    private array $filesToCleanup = [];

    /**
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

        $this->pool = $objectManager->get(SubresourceIntegrityRepositoryPool::class);

        $filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

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
    public function testGetCreatesRepositoryForContext(): void
    {
        $context = 'frontend/Magento/luma/en_US';
        $repository = $this->pool->get($context);

        $this->assertInstanceOf(
            SubresourceIntegrityRepository::class,
            $repository,
            'Pool should return SubresourceIntegrityRepository instance'
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGetReturnsSameInstanceForSameContext(): void
    {
        $context = 'frontend/Magento/luma/en_US';

        $repo1 = $this->pool->get($context);
        $repo2 = $this->pool->get($context);

        $this->assertSame($repo1, $repo2, 'Pool should return same instance for same context');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGetCreatesDifferentInstancesForDifferentContexts(): void
    {
        $contextA = 'frontend/Magento/luma/en_US';
        $contextB = 'frontend/Magento/luma/de_DE';

        $repoA = $this->pool->get($contextA);
        $repoB = $this->pool->get($contextB);

        $this->assertNotSame($repoA, $repoB, 'Pool should return different instances for different contexts');
    }

    /**
     * @magentoAppArea frontend
     */
    public function testMultipleContextsInSameRequest(): void
    {
        $contexts = [
            'frontend/Magento/luma/en_US',
            'frontend/Magento/luma/de_DE',
            'frontend/Magento/luma/fr_FR',
            'frontend/Magento/blank/en_US',
            'adminhtml/Magento/backend/en_US',
        ];

        $objectManager = Bootstrap::getObjectManager();
        $repositories = [];

        // Get repositories for all contexts
        foreach ($contexts as $context) {
            $repositories[$context] = $this->pool->get($context);
        }

        // Save different data to each repository
        foreach ($contexts as $index => $context) {
            $this->filesToCleanup[] = $context . '/' . self::SRI_FILENAME;

            $integrity = $objectManager->create(
                SubresourceIntegrity::class,
                [
                    'data' => [
                        'path' => 'js/test.js',
                        'hash' => "sha256-hash{$index}"
                    ]
                ]
            );

            $repositories[$context]->save($integrity);
        }

        // Verify no data leakage between contexts
        foreach ($contexts as $index => $context) {
            $retrieved = $repositories[$context]->getByPath('js/test.js');
            $this->assertNotNull($retrieved);
            $this->assertEquals(
                "sha256-hash{$index}",
                $retrieved->getHash(),
                "Context {$context} should have its own hash"
            );
        }
    }

    /**
     * @magentoAppArea frontend
     */
    public function testPoolWorksWithBaseArea(): void
    {
        $context = 'base';
        $repository = $this->pool->get($context);

        $this->assertInstanceOf(SubresourceIntegrityRepository::class, $repository);

        // Verify it can store and retrieve data
        $this->filesToCleanup[] = $context . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();
        $integrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/base.js', 'hash' => 'sha256-base']]
        );

        $repository->save($integrity);
        $retrieved = $repository->getByPath('js/base.js');

        $this->assertNotNull($retrieved);
        $this->assertEquals('sha256-base', $retrieved->getHash());
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testPoolWorksWithAdminhtmlArea(): void
    {
        $context = 'adminhtml/Magento/backend/en_US';
        $repository = $this->pool->get($context);

        $this->assertInstanceOf(SubresourceIntegrityRepository::class, $repository);

        // Verify it can store and retrieve data
        $this->filesToCleanup[] = $context . '/' . self::SRI_FILENAME;

        $objectManager = Bootstrap::getObjectManager();
        $integrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/admin.js', 'hash' => 'sha256-admin']]
        );

        $repository->save($integrity);
        $retrieved = $repository->getByPath('js/admin.js');

        $this->assertNotNull($retrieved);
        $this->assertEquals('sha256-admin', $retrieved->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testPoolIsolatesDataBetweenThemes(): void
    {
        $lumaContext = 'frontend/Magento/luma/en_US';
        $blankContext = 'frontend/Magento/blank/en_US';

        $this->filesToCleanup[] = $lumaContext . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $blankContext . '/' . self::SRI_FILENAME;

        $lumaRepo = $this->pool->get($lumaContext);
        $blankRepo = $this->pool->get($blankContext);

        $objectManager = Bootstrap::getObjectManager();

        // Save to Luma
        $lumaIntegrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/theme.js', 'hash' => 'sha256-luma']]
        );
        $lumaRepo->save($lumaIntegrity);

        // Save to Blank
        $blankIntegrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/theme.js', 'hash' => 'sha256-blank']]
        );
        $blankRepo->save($blankIntegrity);

        // Verify each has its own data
        $lumaRetrieved = $lumaRepo->getByPath('js/theme.js');
        $this->assertEquals('sha256-luma', $lumaRetrieved->getHash());

        $blankRetrieved = $blankRepo->getByPath('js/theme.js');
        $this->assertEquals('sha256-blank', $blankRetrieved->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testPoolIsolatesDataBetweenLocales(): void
    {
        $enContext = 'frontend/Magento/luma/en_US';
        $deContext = 'frontend/Magento/luma/de_DE';

        $this->filesToCleanup[] = $enContext . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $deContext . '/' . self::SRI_FILENAME;

        $enRepo = $this->pool->get($enContext);
        $deRepo = $this->pool->get($deContext);

        $objectManager = Bootstrap::getObjectManager();

        // Save to English locale
        $enIntegrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/locale.js', 'hash' => 'sha256-english']]
        );
        $enRepo->save($enIntegrity);

        // Save to German locale
        $deIntegrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/locale.js', 'hash' => 'sha256-german']]
        );
        $deRepo->save($deIntegrity);

        $this->assertEquals('sha256-english', $enRepo->getByPath('js/locale.js')->getHash());
        $this->assertEquals('sha256-german', $deRepo->getByPath('js/locale.js')->getHash());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testPoolHandlesComplexContextPaths(): void
    {
        $contexts = [
            'base/Magento/base/default',
            'frontend/Magento/base/default',
            'adminhtml/Magento/base/default',
            'frontend/Vendor/CustomTheme/en_US',
        ];

        foreach ($contexts as $context) {
            $repository = $this->pool->get($context);
            $this->assertInstanceOf(
                SubresourceIntegrityRepository::class,
                $repository,
                "Pool should handle context: {$context}"
            );
        }
    }

    /**
     * @magentoAppArea frontend
     */
    public function testRepositoryPoolDoesNotLeakBetweenAreas(): void
    {
        $frontendContext = 'frontend/Magento/luma/en_US';
        $adminhtmlContext = 'adminhtml/Magento/backend/en_US';

        $this->filesToCleanup[] = $frontendContext . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $adminhtmlContext . '/' . self::SRI_FILENAME;

        $frontendRepo = $this->pool->get($frontendContext);
        $adminhtmlRepo = $this->pool->get($adminhtmlContext);

        $objectManager = Bootstrap::getObjectManager();

        // Save to frontend area
        $frontendIntegrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/area.js', 'hash' => 'sha256-frontend']]
        );
        $frontendRepo->save($frontendIntegrity);

        // Save to adminhtml area
        $adminhtmlIntegrity = $objectManager->create(
            SubresourceIntegrity::class,
            ['data' => ['path' => 'js/area.js', 'hash' => 'sha256-adminhtml']]
        );
        $adminhtmlRepo->save($adminhtmlIntegrity);

        // Verify no leakage
        $this->assertEquals('sha256-frontend', $frontendRepo->getByPath('js/area.js')->getHash());
        $this->assertEquals('sha256-adminhtml', $adminhtmlRepo->getByPath('js/area.js')->getHash());
    }
}
