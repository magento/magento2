<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer as PageConfigRenderer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test: custom module JS appears in rendered page head with an integrity attribute.
 *
 * This test exercises the full render-time SRI path:
 *   1. A known hash is pre-seeded for the custom module JS into the on-disk sri-hashes.json.
 *   2. The JS asset is registered in the Page Config asset collection.
 *   3. The Page Config Renderer renders the head assets.
 *   4. AddDefaultPropertiesToGroupPlugin intercepts GroupedCollection::getFilteredProperties(),
 *      calls HashResolver::getHashByPath(), finds the hash, and injects integrity + crossorigin
 *      into the group properties.
 *   5. The renderer emits <script … integrity="sha256-…" crossorigin="anonymous" src="…"> for
 *      the custom JS.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoComponentsDir Magento/Csp/_modules
 * @magentoConfigFixture current_store general/locale/code en_US
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SriHeadRendererIntegrationTest extends TestCase
{
    /** Module-relative path used to register the asset with Page Config. */
    private const CUSTOM_JS_PATH = 'Magento_SriTestModule/js/sri-test-widget.js';

    /** Filename fragment used to identify the asset's <script> tag in rendered HTML. */
    private const CUSTOM_JS_FILENAME = 'sri-test-widget.js';

    /** Name of the per-context SRI hash storage file. */
    private const SRI_FILENAME = 'sri-hashes.json';

    /** Stable test hash; value is irrelevant — only the format matters for this test. */
    private const TEST_HASH = 'sha256-dGVzdGhhc2g=';

    /** @var Http */
    private Http $request;

    /** @var WriteInterface */
    private WriteInterface $staticDir;

    /** @var array<string> Files written by this test; deleted in tearDown. */
    private array $filesToCleanup = [];

    /** @var string */
    private string $prevMode;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->prevMode = $objectManager->get(State::class)->getMode();
        $objectManager->get(State::class)->setMode(State::MODE_PRODUCTION);

        $this->request   = $objectManager->get(Http::class);
        $filesystem      = $objectManager->get(Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        $this->ensureDeploymentVersionExists($filesystem);
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

        Bootstrap::getObjectManager()->get(State::class)->setMode($this->prevMode);

        parent::tearDown();
    }

    /**
     * Verifies that the custom module JS is rendered with an integrity attribute on checkout pages.
     *
     * The test seeds a known hash for the custom JS into the per-context sri-hashes.json that
     * HashResolver will read, then renders the page head and asserts that:
     *   - The <script> tag for sri-test-widget.js is present in the output.
     *   - That tag carries integrity="<TEST_HASH>" and crossorigin="anonymous".
     *
     * @magentoDbIsolation disabled
     */
    public function testCustomModuleJsAppearsInRenderedHeadWithIntegrity(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // ── 1. Resolve the deployed path the asset will use in this test environment ──
        //
        // Asset::getPath() returns the context-prefixed path, e.g.
        //   frontend/Magento/luma/en_US/Magento_SriTestModule/js/sri-test-widget.js
        //
        // We derive both the context directory (first 4 segments) and the full path
        // dynamically, so the test works regardless of the active theme or locale.
        $assetRepo  = $objectManager->get(AssetRepository::class);
        $asset      = $assetRepo->createAsset(self::CUSTOM_JS_PATH);
        $assetPath  = $asset->getPath();
        $pathParts  = explode('/', $assetPath);

        $this->assertGreaterThanOrEqual(
            4,
            count($pathParts),
            'Asset path must contain at least area/vendor/theme/locale segments'
        );

        $context = implode('/', array_slice($pathParts, 0, 4));

        // ── 2. Pre-seed the hash file that HashResolver will read ──
        $this->seedSriHashFile($context, [$assetPath => self::TEST_HASH]);

        // ── 3. Configure the request to a checkout (payment) page ──
        //
        // AddDefaultPropertiesToGroupPlugin only injects integrity attributes when
        // SriEnabledActions::isPaymentPageAction() returns true, which requires the
        // route to be checkout_index_index.
        $this->request->setRouteName('checkout');
        $this->request->setControllerName('index');
        $this->request->setActionName('index');

        // ── 4. Register the custom JS with the Page Config asset collection ──
        $pageConfig = $objectManager->get(PageConfig::class);
        $pageConfig->addPageAsset(self::CUSTOM_JS_PATH);

        // ── 5. Render head assets via the Page Config Renderer ──
        $renderer = $objectManager->get(PageConfigRenderer::class);
        $html     = $renderer->renderAssets($renderer->getAvailableResultGroups());

        // ── 6. Assert the <script> tag for the custom JS is present ──
        $this->assertStringContainsString(
            self::CUSTOM_JS_FILENAME,
            $html,
            sprintf(
                'Rendered head HTML must contain a <script> tag for "%s". '
                . 'If this fails, the asset was not added to Page Config correctly.',
                self::CUSTOM_JS_FILENAME
            )
        );

        // ── 7. Assert the integrity attribute is present with the correct value ──
        $this->assertStringContainsString(
            sprintf('integrity="%s"', self::TEST_HASH),
            $html,
            sprintf(
                'Rendered head HTML must contain integrity="%s" for "%s". '
                . 'If this fails, AddDefaultPropertiesToGroupPlugin did not inject the attribute '
                . '(HashResolver may not have found the hash, or the page is not recognised as a '
                . 'payment page).',
                self::TEST_HASH,
                self::CUSTOM_JS_FILENAME
            )
        );

        // ── 8. Assert crossorigin is also present (required for integrity to be enforced) ──
        $this->assertStringContainsString(
            'crossorigin="anonymous"',
            $html,
            'Rendered head HTML must contain crossorigin="anonymous" alongside the integrity attribute'
        );

        // ── 9. Assert integrity and src are on the same <script> tag ──
        //
        // This guards against the degenerate case where two separate <script> tags each
        // satisfy one assertion but neither tag has both attributes.
        $this->assertMatchesRegularExpression(
            '/(?:<script\b[^>]*\bintegrity="' . preg_quote(self::TEST_HASH, '/') . '"[^>]*'
            . '\bsrc="[^"]*' . preg_quote(self::CUSTOM_JS_FILENAME, '/') . '[^"]*"[^>]*>'
            . '|<script\b[^>]*\bsrc="[^"]*' . preg_quote(self::CUSTOM_JS_FILENAME, '/') . '[^"]*"[^>]*'
            . '\bintegrity="' . preg_quote(self::TEST_HASH, '/') . '"[^>]*>)/',
            $html,
            sprintf(
                'The integrity attribute and the src for "%s" must appear on the same <script> tag.',
                self::CUSTOM_JS_FILENAME
            )
        );
    }

    /**
     * Verifies that integrity is NOT injected on non-payment pages.
     *
     * This is a negative assertion: AddDefaultPropertiesToGroupPlugin must be a no-op
     * outside SRI-enabled actions so that normal pages are not broken by unexpected
     * integrity attributes on assets whose hashes may not be available.
     *
     * @magentoDbIsolation disabled
     */
    public function testCustomModuleJsHasNoIntegrityOnNonPaymentPage(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Seed a hash so we know the resolver *could* find it if asked.
        $assetRepo = $objectManager->get(AssetRepository::class);
        $asset     = $assetRepo->createAsset(self::CUSTOM_JS_PATH);
        $assetPath = $asset->getPath();
        $context   = implode('/', array_slice(explode('/', $assetPath), 0, 4));
        $this->seedSriHashFile($context, [$assetPath => self::TEST_HASH]);

        // Set request to a non-payment page (product detail page).
        $this->request->setRouteName('catalog');
        $this->request->setControllerName('product');
        $this->request->setActionName('view');

        $pageConfig = $objectManager->get(PageConfig::class);
        $pageConfig->addPageAsset(self::CUSTOM_JS_PATH);

        $renderer = $objectManager->get(PageConfigRenderer::class);
        $html     = $renderer->renderAssets($renderer->getAvailableResultGroups());

        $this->assertStringContainsString(
            self::CUSTOM_JS_FILENAME,
            $html,
            'Custom JS must still appear on non-payment pages (just without integrity)'
        );

        $this->assertStringNotContainsString(
            'integrity=',
            $html,
            'integrity attribute must NOT be present on non-payment pages'
        );
    }

    /**
     * Writes a per-context sri-hashes.json so HashResolver can find the hash.
     *
     * @param string $context  e.g. frontend/Magento/luma/en_US
     * @param array<string, string> $hashes  path => hash map
     */
    private function seedSriHashFile(string $context, array $hashes): void
    {
        $filePath = $context . '/' . self::SRI_FILENAME;
        $this->filesToCleanup[] = $filePath;

        if (!$this->staticDir->isExist($context)) {
            $this->staticDir->create($context);
        }

        $this->staticDir->writeFile($filePath, json_encode($hashes));
    }

    /**
     * Ensures that deployed_version.txt exists so that versioned asset URLs resolve correctly.
     *
     * @param Filesystem $filesystem
     */
    private function ensureDeploymentVersionExists(Filesystem $filesystem): void
    {
        try {
            $dir         = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
            $versionFile = 'deployed_version.txt';

            if (!$dir->isExist($versionFile)) {
                $dir->writeFile($versionFile, (string)time());
            }
        } catch (\Exception $e) {
            // Non-fatal — versioned URLs are not required for the assertions to pass.
        }
    }
}
