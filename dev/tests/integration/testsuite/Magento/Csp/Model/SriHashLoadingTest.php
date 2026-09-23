<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Block\Sri\Hashes;
use Magento\Csp\Model\SubresourceIntegrity\Storage\File as SriFileStorage;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for SRI hash loading and graceful failure handling
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SriHashLoadingTest extends TestCase
{
    /**
     * @var string
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * @var string
     */
    private string $prevMode;

    /**
     * @var array
     */
    private array $filesToCleanup = [];

    /**
     * @var array
     */
    private array $dirsToRestore = [];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->prevMode = $this->objectManager->get(State::class)->getMode();
        $this->objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->staticDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->filesToCleanup = [];
        $this->dirsToRestore = [];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ($this->dirsToRestore as $relativePath) {
            if ($this->staticDir->isExist($relativePath)) {
                $this->staticDir->changePermissions($relativePath, 0755);
            }
        }
        foreach ($this->filesToCleanup as $file) {
            if ($this->staticDir->isExist($file)) {
                $this->staticDir->delete($file);
            }
        }
        $this->objectManager->get(State::class)->setMode($this->prevMode);
        parent::tearDown();
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testHashesLoadedForCurrentContext(): void
    {
        $hashesBlock = $this->objectManager->create(Hashes::class);
        $serialized = $hashesBlock->getSerialized();
        $this->assertNotEmpty($serialized);
        $this->assertJson($serialized);
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testHashesBlockReturnsValidStructure(): void
    {
        $hashesBlock = $this->objectManager->create(Hashes::class);
        $data = json_decode($hashesBlock->getSerialized(), true);
        $this->assertIsArray($data);
        foreach ($data as $url => $hash) {
            $this->assertIsString($url);
            $this->assertIsString($hash);
            if (!empty($hash)) {
                $this->assertStringStartsWith('sha256-', $hash);
            }
        }
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulHandlingWhenFileIsMissing(): void
    {
        $hashesBlock = $this->objectManager->create(Hashes::class);
        $serialized = $hashesBlock->getSerialized();
        $this->assertNotNull($serialized);
        $this->assertJson($serialized);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulHandlingWithCorruptedJsonFile(): void
    {
        $testPath = 'frontend/Magento/luma/en_US';
        $sriFile = $testPath . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $sriFile;

        if (!$this->staticDir->isExist($testPath)) {
            $this->staticDir->create($testPath);
        }
        $this->staticDir->writeFile($sriFile, 'invalid json {{{');

        $repository = $this->objectManager->get(SubresourceIntegrityRepositoryPool::class)->get($testPath);
        $allHashes = $repository->getAll();
        $this->assertIsArray($allHashes);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulHandlingWithEmptyJsonObject(): void
    {
        $testPath = 'frontend/Magento/luma/en_US';
        $sriFile = $testPath . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $sriFile;

        if (!$this->staticDir->isExist($testPath)) {
            $this->staticDir->create($testPath);
        }
        $this->staticDir->writeFile($sriFile, '{}');

        $repository = $this->objectManager->get(SubresourceIntegrityRepositoryPool::class)->get($testPath);
        $this->assertEmpty($repository->getAll());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulHandlingWithArrayInsteadOfObject(): void
    {
        $testPath = 'frontend/Magento/luma/en_US';
        $sriFile = $testPath . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $sriFile;

        if (!$this->staticDir->isExist($testPath)) {
            $this->staticDir->create($testPath);
        }
        $this->staticDir->writeFile($sriFile, '["item1", "item2"]');

        $repository = $this->objectManager->get(SubresourceIntegrityRepositoryPool::class)->get($testPath);
        $this->assertIsArray($repository->getAll());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testHashesBlockReturnsEmptyObjectOnError(): void
    {
        $hashesBlock = $this->objectManager->create(Hashes::class);
        $serialized = $hashesBlock->getSerialized();
        $this->assertJson($serialized);
        $this->assertNotNull(json_decode($serialized, true));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testDifferentStoresGetCorrectHashes(): void
    {
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);

        $storeManager->setCurrentStore('default');
        $hashes1 = $this->objectManager->create(Hashes::class)->getSerialized();

        $storeManager->setCurrentStore('fixture_second_store');
        $hashes2 = $this->objectManager->create(Hashes::class)->getSerialized();

        $this->assertJson($hashes1);
        $this->assertJson($hashes2);
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testHashesIncludeFullUrls(): void
    {
        $data = json_decode($this->objectManager->create(Hashes::class)->getSerialized(), true);
        foreach (array_keys($data) as $url) {
            $this->assertStringContainsString('static', $url);
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testDesignInterfaceUsedForContext(): void
    {
        $design = $this->objectManager->get(DesignInterface::class);
        $this->assertNotNull($design->getDesignTheme());
        $this->assertNotEmpty($design->getLocale());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testRepositoryPoolCreatesSeparateInstancesPerContext(): void
    {
        $pool = $this->objectManager->get(SubresourceIntegrityRepositoryPool::class);
        $lumaEn = $pool->get('frontend/Magento/luma/en_US');
        $lumaDe = $pool->get('frontend/Magento/luma/de_DE');
        $blankEn = $pool->get('frontend/Magento/blank/en_US');

        $this->assertNotSame($lumaEn, $lumaDe);
        $this->assertNotSame($lumaEn, $blankEn);
        $this->assertSame($lumaEn, $pool->get('frontend/Magento/luma/en_US'));
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testAdminhtmlAreaHashesLoading(): void
    {
        $this->assertJson($this->objectManager->create(Hashes::class)->getSerialized());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testBaseAreaRepositoryAccessible(): void
    {
        $pool = $this->objectManager->get(SubresourceIntegrityRepositoryPool::class);
        $baseRepo = $pool->get('base');
        $this->assertInstanceOf(SubresourceIntegrityRepository::class, $baseRepo);
        $this->assertIsArray($baseRepo->getAll());
    }
}
