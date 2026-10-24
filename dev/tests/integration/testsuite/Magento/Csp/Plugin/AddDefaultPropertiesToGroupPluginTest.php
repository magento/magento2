<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Remote;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration tests for AddDefaultPropertiesToGroupPlugin
 *
 * Tests the plugin that adds SRI integrity attributes to JS assets on payment pages.
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AddDefaultPropertiesToGroupPluginTest extends TestCase
{
    /**
     * Name of the SRI hash storage file
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * Hash for testing
     */
    private const TEST_HASH = 'sha256-abc123def456';

    /**
     * Constant for Payment Action
     */
    private const PAYMENT_ACTION = 'checkout_index_index';

    /**
     * Constant for Non Payment Action
     */
    private const NON_PAYMENT_ACTION = 'catalog_product_view';

    /**
     * @var AddDefaultPropertiesToGroupPlugin
     */
    private AddDefaultPropertiesToGroupPlugin $plugin;

    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var GroupedCollection
     */
    private GroupedCollection $groupedCollection;

    /**
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * @var SubresourceIntegrityRepositoryPool
     */
    private SubresourceIntegrityRepositoryPool $repositoryPool;

    /**
     * @var HashResolverInterface
     */
    private HashResolverInterface $hashResolver;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

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

        $this->request = $objectManager->get(Http::class);
        $this->groupedCollection = $objectManager->create(GroupedCollection::class);
        $this->repositoryPool = $objectManager->get(SubresourceIntegrityRepositoryPool::class);
        $this->hashResolver = $objectManager->get(HashResolverInterface::class);
        $this->logger = $objectManager->get(LoggerInterface::class);

        $filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        // Create deployment version file for CI/CD environments
        $this->ensureDeploymentVersionExists($filesystem);

        // Create plugin instance with real dependencies
        $this->plugin = $objectManager->create(AddDefaultPropertiesToGroupPlugin::class);

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
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testAddsIntegrityAttributeOnPaymentPage(): void
    {
        // Create SRI hash file
        $context = 'frontend/Magento/luma/en_US';
        $assetPath = 'js/test-asset.js';  // Use relative path
        $fullAssetPath = $context . '/' . $assetPath;
        $this->createSriHashFile($context, [$fullAssetPath => self::TEST_HASH]);

        // Set request to payment page
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        // Create a local JS asset
        $asset = $this->createLocalJsAsset($assetPath);

        // Call plugin
        $properties = [];
        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert integrity attributes added
        $this->assertArrayHasKey('attributes', $resultProperties);
        $this->assertArrayHasKey('integrity', $resultProperties['attributes']);
        $this->assertArrayHasKey('crossorigin', $resultProperties['attributes']);
        $this->assertEquals(self::TEST_HASH, $resultProperties['attributes']['integrity']);
        $this->assertEquals('anonymous', $resultProperties['attributes']['crossorigin']);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testSkipsNonPaymentPages(): void
    {
        // Set request to non-payment page
        $this->request->setActionName('view');
        $this->request->setControllerName('product');
        $this->request->setRouteName('catalog');

        $asset = $this->createLocalJsAsset('js/test.js');
        $properties = [];

        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert NO integrity attributes added
        $this->assertArrayNotHasKey('integrity', $resultProperties['attributes'] ?? []);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testOnlyProcessesLocalInterfaceAssets(): void
    {
        // Set request to payment page
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        // Create a REMOTE asset (CDN)
        $objectManager = Bootstrap::getObjectManager();
        $asset = $objectManager->create(
            Remote::class,
            [
                'url' => 'https://cdn.example.com/jquery.min.js',
                'contentType' => 'js'
            ]
        );

        $properties = [];
        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert NO integrity attributes added (remote assets skipped)
        $this->assertArrayNotHasKey('integrity', $resultProperties['attributes'] ?? []);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testGracefulDegradationWhenHashNotFound(): void
    {
        // Set request to payment page
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        // Create asset but NO hash file
        $asset = $this->createLocalJsAsset('js/nonexistent.js');
        $properties = [];

        [$resultAsset, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert asset still returned, but NO integrity attribute
        $this->assertInstanceOf(LocalInterface::class, $resultAsset);
        $this->assertArrayNotHasKey('integrity', $resultProperties['attributes'] ?? []);
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testMultipleAssetsOnSamePage(): void
    {
        // Create multiple hash files
        $context = 'frontend/Magento/luma/en_US';
        $hashes = [
            'frontend/Magento/luma/en_US/js/asset1.js' => 'sha256-hash1',
            'frontend/Magento/luma/en_US/js/asset2.js' => 'sha256-hash2',
            'frontend/Magento/luma/en_US/js/asset3.js' => 'sha256-hash3',
        ];
        $this->createSriHashFile($context, $hashes);

        // Set request to payment page
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        // Test each asset gets correct hash
        $assetPaths = ['js/asset1.js', 'js/asset2.js', 'js/asset3.js'];
        $expectedHashes = ['sha256-hash1', 'sha256-hash2', 'sha256-hash3'];

        foreach ($assetPaths as $index => $path) {
            $asset = $this->createLocalJsAsset($path);
            $properties = [];

            [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
                $this->groupedCollection,
                $asset,
                $properties
            );

            $this->assertEquals(
                $expectedHashes[$index],
                $resultProperties['attributes']['integrity'],
                "Asset {$path} should have correct hash"
            );
        }
    }

    /**
     * @magentoAppArea frontend
     */
    public function testInProductionMode(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        // Set request to payment page
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        $context = 'frontend/Magento/luma/en_US';
        $assetPath = 'js/prod-test.js';
        $fullPath = $context . '/' . $assetPath;
        $this->createSriHashFile($context, [$fullPath => self::TEST_HASH]);

        $asset = $this->createLocalJsAsset($assetPath);
        $properties = [];

        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert plugin executes in production mode
        $this->assertArrayHasKey('integrity', $resultProperties['attributes']);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testInDeveloperMode(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setMode(State::MODE_DEVELOPER);

        // Set request to payment page (should still work)
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        $context = 'frontend/Magento/luma/en_US';
        $assetPath = 'js/dev-test.js';
        $fullPath = $context . '/' . $assetPath;
        $this->createSriHashFile($context, [$fullPath => self::TEST_HASH]);

        $asset = $this->createLocalJsAsset($assetPath);
        $properties = [];

        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert plugin still respects payment page restriction
        $this->assertArrayHasKey('integrity', $resultProperties['attributes']);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testDoesNotOverwriteExistingProperties(): void
    {
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        $context = 'frontend/Magento/luma/en_US';
        $assetPath = 'js/test.js';
        $fullPath = $context . '/' . $assetPath;
        $this->createSriHashFile($context, [$fullPath => self::TEST_HASH]);

        $asset = $this->createLocalJsAsset($assetPath);

        // Properties already exist
        $properties = [
            'attributes' => [
                'defer' => true,
                'data-custom' => 'value'
            ]
        ];

        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            $properties
        );

        // Assert existing properties preserved AND integrity added
        $this->assertTrue($resultProperties['attributes']['defer']);
        $this->assertEquals('value', $resultProperties['attributes']['data-custom']);
        $this->assertEquals(self::TEST_HASH, $resultProperties['attributes']['integrity']);
    }

    /**
     * @magentoAppArea frontend
     */
    public function testWithDifferentPaymentActions(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Override SriEnabledActions to include multiple payment actions
        $sriEnabledActions = $objectManager->create(
            SriEnabledActions::class,
            [
                'paymentActions' => [
                    'checkout_index_index',
                    'checkout_cart_index',
                    'sales_order_view'
                ]
            ]
        );

        $plugin = $objectManager->create(
            AddDefaultPropertiesToGroupPlugin::class,
            ['action' => $sriEnabledActions]
        );

        $context = 'frontend/Magento/luma/en_US';
        $assetPath = 'js/test.js';
        $fullPath = $context . '/' . $assetPath;
        $this->createSriHashFile($context, [$fullPath => self::TEST_HASH]);
        $asset = $this->createLocalJsAsset($assetPath);

        // Test checkout page
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        [, $resultProperties] = $plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            []
        );
        $this->assertArrayHasKey('integrity', $resultProperties['attributes']);

        // Test cart page
        $this->request->setActionName('index');
        $this->request->setControllerName('cart');
        $this->request->setRouteName('checkout');

        [, $resultProperties] = $plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            []
        );
        $this->assertArrayHasKey('integrity', $resultProperties['attributes']);

        // Test order view page
        $this->request->setActionName('view');
        $this->request->setControllerName('order');
        $this->request->setRouteName('sales');

        [, $resultProperties] = $plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            []
        );
        $this->assertArrayHasKey('integrity', $resultProperties['attributes']);
    }

    /**
     * When JS merging is enabled, the plugin must not add integrity attributes to individual
     * assets — each asset would form its own group, breaking the merge entirely.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store dev/js/merge_files 1
     */
    public function testSkipsIntegrityWhenJsMergingIsEnabled(): void
    {
        $context = 'frontend/Magento/luma/en_US';
        $assetPath = 'js/merge-test.js';
        $fullPath = $context . '/' . $assetPath;
        $this->createSriHashFile($context, [$fullPath => self::TEST_HASH]);

        // Set request to payment page — integrity would normally be added here
        $this->request->setActionName('index');
        $this->request->setControllerName('index');
        $this->request->setRouteName('checkout');

        $asset = $this->createLocalJsAsset($assetPath);
        [, $resultProperties] = $this->plugin->beforeGetFilteredProperties(
            $this->groupedCollection,
            $asset,
            []
        );

        $this->assertArrayNotHasKey(
            'integrity',
            $resultProperties['attributes'] ?? [],
            'integrity attribute must not be added when JS merging is enabled'
        );
    }

    /**
     * Create SRI hash file for testing
     *
     * @param string $context
     * @param array $hashes
     * @return void
     */
    private function createSriHashFile(string $context, array $hashes): void
    {
        $filePath = $context . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        if (!$this->staticDir->isExist($context)) {
            $this->staticDir->create($context);
        }

        $this->staticDir->writeFile($filePath, json_encode($hashes));
    }

    /**
     * Ensures deployment version file exists for asset URL generation
     *
     * @param Filesystem $filesystem
     * @return void
     */
    private function ensureDeploymentVersionExists(Filesystem $filesystem): void
    {
        try {
            $staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
            $versionFile = 'deployed_version.txt';

            if (!$staticDir->isExist($versionFile)) {
                $staticDir->writeFile($versionFile, (string)time());
            }
        } catch (\Exception $e) {
            // Silently fail - if this doesn't work, tests will show the error
        }
    }

    /**
     * Create a local JS asset for testing
     *
     * @param string $path
     * @return LocalInterface
     */
    private function createLocalJsAsset(string $path): LocalInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->get(\Magento\Framework\View\Asset\Repository::class);

        return $assetRepo->createAsset($path);
    }
}
