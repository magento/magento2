<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Magento\Deploy\Console\DeployStaticOptions as Options;
use Magento\Deploy\Service\DeployStaticContent;
use Magento\Deploy\Strategy\DeployStrategyFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Utility\Files as UtilityFiles;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Verifies that a custom module's JS file has its SRI hash computed at deploy time
 * and that the hash appears in window.sriHashes (via HashResolver::getAllHashes()),
 * which is what sri.js uses to set the integrity attribute in the DOM.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoComponentsDir Magento/Csp/_modules
 * @magentoDataFixture Magento/Deploy/_files/theme.php
 * @group slow
 * @group sri_deployment
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SriCustomModuleJsIntegrityTest extends TestCase
{
    /** @var string SRI hash manifest written by the deploy process. */
    private const SRI_FILENAME = 'sri-hashes.json';

    /** @var string Module name as registered in ComponentRegistrar and ModuleList. */
    private const MODULE_NAME = 'Magento_SriTestModule';

    /** @var string Basename of the JS asset deployed by the test module. */
    private const CUSTOM_JS_FILENAME = 'sri-test-widget.js';

    /** @var string Require.js path used to load the JS asset. */
    private const CUSTOM_JS_PATH = 'Magento_SriTestModule/js/sri-test-widget.js';

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
     * Saved ModuleList::$configData before test module injection, for tearDown restore.
     *
     * @var array|null
     */
    private ?array $originalModuleConfigData = null;

    /**
     * Whether setUp() registered the test module in ComponentRegistrar (needs tearDown cleanup).
     *
     * @var bool
     */
    private bool $registeredTestModule = false;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->injectModuleIntoModuleList($objectManager);
        $this->ensureModuleInComponentRegistrar();
        $this->clearStaticFilesCache();
        $this->initAppState($objectManager);
        $this->filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir  = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->delete(DirectoryList::TMP_MATERIALIZATION_DIR);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->restoreModuleList($objectManager);
        $this->restoreComponentRegistrar();
        $objectManager->get(State::class)->setMode($this->prevMode);
        $this->filesystem->getDirectoryWrite(DirectoryList::PUB)->delete(DirectoryList::STATIC_VIEW);
        $this->staticDir->getDriver()->createDirectory($this->staticDir->getAbsolutePath());
        parent::tearDown();
    }

    /**
     * Verifies custom module JS hash is computed and stored after a standard deploy.
     *
     * @magentoDbIsolation disabled
     */
    public function testCustomModuleJsHashIsStoredOnStandardDeploy(): void
    {
        $this->runDeploy(DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD);
        $this->assertCustomJsHashInSriFile('frontend/Magento/zoom1/en_US/' . self::SRI_FILENAME);
    }

    /**
     * Verifies custom module JS hash is computed and stored after a compact deploy.
     *
     * @magentoDbIsolation disabled
     */
    public function testCustomModuleJsHashIsStoredOnCompactDeploy(): void
    {
        $this->runDeploy(DeployStrategyFactory::DEPLOY_STRATEGY_COMPACT);

        // Compact deploys shared assets to base/default packages
        $sriFileCandidates = [
            'frontend/Magento/zoom1/en_US/' . self::SRI_FILENAME,
            'frontend/Magento/zoom1/default/' . self::SRI_FILENAME,
            'frontend/Magento/base/default/' . self::SRI_FILENAME,
            'base/Magento/base/default/' . self::SRI_FILENAME,
        ];

        $found = false;
        foreach ($sriFileCandidates as $candidate) {
            if ($this->staticDir->isExist($candidate)) {
                $data = $this->readSriFile($candidate);
                if ($this->findCustomJsKey($data) !== null) {
                    $found = true;
                    break;
                }
            }
        }

        $this->assertTrue(
            $found,
            sprintf(
                'Custom module JS "%s" hash must appear in at least one sri-hashes.json'
                . ' under compact deploy base packages',
                self::CUSTOM_JS_PATH
            )
        );
    }

    /**
     * Verifies HashResolver::getAllHashes() returns a URL-keyed entry for the custom module JS.
     *
     * This is the data that populates window.sriHashes in the browser. If this entry is present
     * and correct, sri.js will set the integrity attribute on the script element in the DOM.
     *
     * @magentoDbIsolation disabled
     */
    public function testCustomModuleJsHashAppearsInGetAllHashes(): void
    {
        $this->runDeploy(DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD);

        $objectManager = Bootstrap::getObjectManager();
        Glob::clearCache();

        $freshPool    = $objectManager->create(SubresourceIntegrityRepositoryPool::class);
        $hashResolver = $objectManager->create(
            HashResolverInterface::class,
            ['repositoryPool' => $freshPool]
        );

        $allHashes = $hashResolver->getAllHashes();

        $matchingKeys = array_filter(
            array_keys($allHashes),
            static fn(string $url): bool => str_ends_with($url, self::CUSTOM_JS_FILENAME)
        );

        $this->assertNotEmpty(
            $matchingKeys,
            sprintf(
                '"%s" must appear as a URL-keyed entry in HashResolver::getAllHashes(). '
                . 'This is the data used by sri.js to set the integrity attribute in the DOM.',
                self::CUSTOM_JS_FILENAME
            )
        );

        foreach ($matchingKeys as $url) {
            $hash = $allHashes[$url];
            $this->assertStringStartsWith(
                'sha256-',
                $hash,
                sprintf('Hash for %s must be a valid sha256 value', $url)
            );
            $this->assertMatchesRegularExpression(
                '/^sha256-[A-Za-z0-9+\/]+=*$/',
                $hash,
                sprintf('Hash for %s must be a valid base64-encoded sha256', $url)
            );
        }
    }

    /**
     * Verifies the hash stored in sri-hashes.json matches the actual deployed file content.
     *
     * A hash mismatch between the stored value and the served file would cause the browser
     * to block the script when sri.js applies the integrity attribute.
     *
     * @magentoDbIsolation disabled
     */
    public function testCustomModuleJsHashMatchesDeployedFileContent(): void
    {
        $this->runDeploy(DeployStrategyFactory::DEPLOY_STRATEGY_STANDARD);

        $sriFilePath = 'frontend/Magento/zoom1/en_US/' . self::SRI_FILENAME;

        if (!$this->staticDir->isExist($sriFilePath)) {
            $this->markTestSkipped("SRI file not found at {$sriFilePath} after standard deploy");
        }

        $data       = $this->readSriFile($sriFilePath);
        $storedPath = $this->findCustomJsKey($data);

        $this->assertNotNull(
            $storedPath,
            sprintf('"%s" must have a hash entry in %s', self::CUSTOM_JS_PATH, $sriFilePath)
        );

        $storedHash   = $data[$storedPath];
        $deployedFile = $storedPath;

        $this->assertTrue(
            $this->staticDir->isExist($deployedFile),
            "Deployed JS file must exist at: {$deployedFile}"
        );

        $content      = $this->staticDir->readFile($deployedFile);
        $expectedHash = 'sha256-' . base64_encode(hash('sha256', $content, true));

        $this->assertSame(
            $expectedHash,
            $storedHash,
            sprintf(
                'Stored hash for "%s" must match the actual file content. '
                . 'A mismatch means the browser would block the script when sri.js applies the integrity attribute.',
                $storedPath
            )
        );
    }

    /**
     * Injects the test module into ModuleList::$configData so Deploy\Collector::isEnabled() passes.
     *
     * The sandbox config.php does not list Magento_SriTestModule; without this the deploy skips it.
     * @magentoAppIsolation resets the DI container between tests, so each setUp starts fresh.
     *
     * @param ObjectManagerInterface $objectManager
     */
    private function injectModuleIntoModuleList(ObjectManagerInterface $objectManager): void
    {
        $moduleList = $objectManager->get(ModuleList::class);
        $moduleList->has(self::MODULE_NAME);
        $reflection     = new ReflectionClass($moduleList);
        $configDataProp = $reflection->getProperty('configData');
        $configData     = $configDataProp->getValue($moduleList) ?? [];
        $this->originalModuleConfigData = $configData;
        $configData[self::MODULE_NAME]  = 1;
        $configDataProp->setValue($moduleList, $configData);
    }

    /**
     * Restores ModuleList::$configData to its pre-test state.
     *
     * @param ObjectManagerInterface $objectManager
     */
    private function restoreModuleList(ObjectManagerInterface $objectManager): void
    {
        if ($this->originalModuleConfigData === null) {
            return;
        }
        $moduleList     = $objectManager->get(ModuleList::class);
        $reflection     = new ReflectionClass($moduleList);
        $configDataProp = $reflection->getProperty('configData');
        $configDataProp->setValue($moduleList, $this->originalModuleConfigData);
        $this->originalModuleConfigData = null;
    }

    /**
     * Ensures Magento_SriTestModule is registered in ComponentRegistrar::$paths.
     *
     * @magentoComponentsDir registers the module via ComponentRegistrarFixture::startTest, but in
     * suite sequences the registration can be absent at setUp() time. Injecting directly is reliable.
     */
    private function ensureModuleInComponentRegistrar(): void
    {
        $reflection = new ReflectionClass(ComponentRegistrar::class);
        $pathsProp  = $reflection->getProperty('paths');
        $paths      = $pathsProp->getValue(null) ?? [];
        if (isset($paths[ComponentRegistrar::MODULE][self::MODULE_NAME])) {
            return;
        }
        $paths[ComponentRegistrar::MODULE][self::MODULE_NAME] = dirname(__DIR__) . '/_modules/SriTestModule';
        $pathsProp->setValue(null, $paths);
        $this->registeredTestModule = true;
    }

    /**
     * Removes the test module from ComponentRegistrar::$paths if setUp() added it.
     */
    private function restoreComponentRegistrar(): void
    {
        if (!$this->registeredTestModule) {
            return;
        }
        $reflection = new ReflectionClass(ComponentRegistrar::class);
        $pathsProp  = $reflection->getProperty('paths');
        $paths      = $pathsProp->getValue(null) ?? [];
        unset($paths[ComponentRegistrar::MODULE][self::MODULE_NAME]);
        $pathsProp->setValue(null, $paths);
        $this->registeredTestModule = false;
    }

    /**
     * Clears the Files::$_cache entry for getStaticPreProcessingFiles.
     *
     * This static cache persists across @magentoAppIsolation resets. An earlier deploy test may
     * have populated it before our module was registered, causing the asset scanner to skip it.
     */
    private function clearStaticFilesCache(): void
    {
        $reflection = new ReflectionClass(UtilityFiles::class);
        $cacheProp  = $reflection->getProperty('_cache');
        $cache      = $cacheProp->getValue(null) ?? [];
        unset($cache[UtilityFiles::class . '::getStaticPreProcessingFiles|*']);
        $cacheProp->setValue(null, $cache);
    }

    /**
     * Sets production mode and ensures area code is 'frontend'.
     *
     * In suite context @magentoAppArea does not always set _areaCode before setUp() runs.
     * HashResolver::buildThemeContextPaths() requires a non-null string area.
     *
     * @param ObjectManagerInterface $objectManager
     */
    private function initAppState(ObjectManagerInterface $objectManager): void
    {
        $appState       = $objectManager->get(State::class);
        $this->prevMode = $appState->getMode();
        $appState->setMode(State::MODE_PRODUCTION);
        $stateRef     = new ReflectionClass($appState);
        $areaCodeProp = $stateRef->getProperty('_areaCode');
        if ($areaCodeProp->getValue($appState) === null) {
            $areaCodeProp->setValue($appState, 'frontend');
        }
    }

    /**
     * Runs static content deployment with JS only.
     *
     * @param string $strategy
     */
    private function runDeploy(string $strategy): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $logger        = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $deployService = $objectManager->create(DeployStaticContent::class, ['logger' => $logger]);

        $deployService->deploy([
            Options::DRY_RUN         => false,
            Options::NO_JAVASCRIPT   => false,
            Options::NO_JS_BUNDLE    => true,
            Options::NO_CSS          => true,
            Options::NO_LESS         => true,
            Options::NO_IMAGES       => true,
            Options::NO_FONTS        => true,
            Options::NO_HTML         => true,
            Options::NO_MISC         => true,
            Options::NO_HTML_MINIFY  => true,
            Options::AREA            => ['frontend'],
            Options::EXCLUDE_AREA    => ['none'],
            Options::THEME           => ['Magento/zoom1'],
            Options::EXCLUDE_THEME   => ['none'],
            Options::LANGUAGE        => ['en_US'],
            Options::EXCLUDE_LANGUAGE => ['none'],
            Options::JOBS_AMOUNT     => 1,
            Options::SYMLINK_LOCALE  => false,
            Options::STRATEGY        => $strategy,
            Options::NO_PARENT       => false,
        ]);
    }

    /**
     * Reads and decodes a sri-hashes.json file.
     *
     * @param string $path Relative to static dir
     * @return array<string, string>
     */
    private function readSriFile(string $path): array
    {
        $content = $this->staticDir->readFile($path);
        $data    = json_decode($content, true);

        $this->assertIsArray($data, "Invalid JSON in {$path}");

        return $data;
    }

    /**
     * Finds the path key in sri-hashes.json that corresponds to the custom module's JS.
     *
     * @param array<string, string> $data
     * @return string|null
     */
    private function findCustomJsKey(array $data): ?string
    {
        foreach (array_keys($data) as $path) {
            if (str_ends_with($path, self::CUSTOM_JS_FILENAME)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Asserts the custom module JS hash appears in the given sri-hashes.json file.
     *
     * @param string $sriFilePath Relative to static dir
     */
    private function assertCustomJsHashInSriFile(string $sriFilePath): void
    {
        $this->assertTrue(
            $this->staticDir->isExist($sriFilePath),
            "SRI file must exist at: {$sriFilePath}"
        );

        $data       = $this->readSriFile($sriFilePath);
        $storedPath = $this->findCustomJsKey($data);

        $this->assertNotNull(
            $storedPath,
            sprintf(
                '"%s" must have an SRI hash entry in %s after deploy. '
                . 'Without this, sri.js cannot set the integrity attribute in the DOM.',
                self::CUSTOM_JS_PATH,
                $sriFilePath
            )
        );

        $this->assertStringStartsWith(
            'sha256-',
            $data[$storedPath],
            "Hash for {$storedPath} must be a valid sha256 value"
        );
    }
}
