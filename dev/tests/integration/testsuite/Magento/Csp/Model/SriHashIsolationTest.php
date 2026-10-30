<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Locale\ResolverInterface as LocaleResolverInterface;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that window.sriHashes (via HashResolver::getAllHashes()) only contains
 * hashes scoped to the current theme and locale — not all deployed themes and locales.
 *
 * When multiple themes and locales are deployed, each gets its own sri-hashes.json file.
 * At render time, only the hashes relevant to the current request context should be loaded.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Deploy/_files/theme.php
 */
class SriHashIsolationTest extends TestCase
{
    /**
     * Name of the SRI hashes storage file.
     */
    private const SRI_FILENAME = 'sri-hashes.json';

    /**
     * @var WriteInterface
     */
    private WriteInterface $staticDir;

    /**
     * @var array
     */
    private array $filesToCleanup = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $filesystem = $objectManager->get(Filesystem::class);
        $this->staticDir = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $this->filesToCleanup = [];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        foreach ($this->filesToCleanup as $file) {
            if ($this->staticDir->isExist($file)) {
                $this->staticDir->delete($file);
            }
        }
        parent::tearDown();
    }

    /**
     * Verifies that getAllHashes() for zoom1/en_US does not include hashes from zoom2 or de_DE.
     *
     * When a merchant deploys multiple themes and locales, each theme/locale gets its own
     * sri-hashes.json. At checkout render time, window.sriHashes must only contain hashes
     * for the active theme/locale — not every deployed combination.
     *
     * The deploy-level correctness (per-context file creation) is covered by
     * SriStandardStrategyDeploymentTest. This test verifies the render-time isolation —
     * that getAllHashes() respects the current theme/locale scope.
     *
     * @magentoConfigFixture current_store general/locale/code en_US
     */
    public function testGetAllHashesOnlyContainsCurrentThemeAndLocale(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Write sri-hashes.json files directly — same structure the deploy produces.
        // Simulates a merchant having deployed 2 themes × 2 locales.
        $deployedContexts = [
            'frontend/Magento/zoom1/en_US' => [
                'frontend/Magento/zoom1/en_US/js/file1.js' => 'sha256-zoom1-en-1',
                'frontend/Magento/zoom1/en_US/js/file2.js' => 'sha256-zoom1-en-2',
            ],
            'frontend/Magento/zoom1/de_DE' => [
                'frontend/Magento/zoom1/de_DE/js/file1.js' => 'sha256-zoom1-de-1',
                'frontend/Magento/zoom1/de_DE/js/file2.js' => 'sha256-zoom1-de-2',
            ],
            'frontend/Magento/zoom2/en_US' => [
                'frontend/Magento/zoom2/en_US/js/file1.js' => 'sha256-zoom2-en-1',
                'frontend/Magento/zoom2/en_US/js/file2.js' => 'sha256-zoom2-en-2',
            ],
            'frontend/Magento/zoom2/de_DE' => [
                'frontend/Magento/zoom2/de_DE/js/file1.js' => 'sha256-zoom2-de-1',
                'frontend/Magento/zoom2/de_DE/js/file2.js' => 'sha256-zoom2-de-2',
            ],
        ];

        foreach ($deployedContexts as $contextPath => $hashes) {
            if (!$this->staticDir->isExist($contextPath)) {
                $this->staticDir->create($contextPath);
            }
            $sriFile = $contextPath . '/' . self::SRI_FILENAME;
            $this->staticDir->writeFile($sriFile, json_encode($hashes));
            $this->filesToCleanup[] = $sriFile;
        }

        // Set up the current request as zoom1/en_US (simulating a checkout page load).
        $objectManager->get(LocaleResolverInterface::class)->setLocale('en_US');
        $objectManager->removeSharedInstance(DesignInterface::class);

        $zoom1Theme = $objectManager->create(ThemeCollection::class)
            ->getThemeByFullPath('frontend/Magento/zoom1');
        $this->assertNotNull($zoom1Theme, 'zoom1 theme must be registered');

        $objectManager->get(DesignInterface::class)->setDesignTheme($zoom1Theme, 'frontend');

        // getAllHashes() is what powers window.sriHashes on the checkout page.
        $allHashes = $objectManager->create(HashResolverInterface::class)->getAllHashes();

        $this->assertNotEmpty($allHashes, 'getAllHashes() must return hashes for zoom1/en_US');

        foreach (array_keys($allHashes) as $url) {
            $this->assertStringNotContainsString(
                'zoom2',
                $url,
                "getAllHashes() for zoom1/en_US must not include zoom2 hashes. Found: {$url}"
            );
            $this->assertStringNotContainsString(
                'de_DE',
                $url,
                "getAllHashes() for zoom1/en_US must not include de_DE hashes. Found: {$url}"
            );
        }

        // Confirm zoom1/en_US hashes are actually present in the result.
        $allUrls = implode(' ', array_keys($allHashes));
        $this->assertStringContainsString(
            'zoom1/en_US',
            $allUrls,
            'getAllHashes() must contain at least one zoom1/en_US hash'
        );
    }
}
