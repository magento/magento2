<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Framework\RequireJs\Config as RequireJsConfig;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\RequireJs\Block\Html\Head\Config as RequireJsBlock;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for RepositionSriBeforeRequireJsConfig plugin.
 *
 * Verifies that the plugin is correctly wired via DI and that sri.js is
 * repositioned immediately before requirejs-config.js after setLayout() runs.
 *
 * @magentoAppIsolation enabled
 */
class RepositionSriBeforeRequireJsConfigTest extends TestCase
{
    private const SRI_JS_ID = 'Magento_Csp::js/sri.js';

    /** @var LayoutInterface */
    private LayoutInterface $layout;

    /** @var PageConfig */
    private PageConfig $pageConfig;

    /** @var AssetRepository */
    private AssetRepository $assetRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->layout = $objectManager->get(LayoutInterface::class);
        $this->pageConfig = $objectManager->get(PageConfig::class);
        $this->assetRepository = $objectManager->get(AssetRepository::class);
    }

    /**
     * Verify sri.js is repositioned immediately before requirejs-config.js.
     *
     * Simulates sri.js being added by layout XML before the RequireJs Config block
     * runs, then asserts the plugin moves it to the correct position.
     *
     * @magentoAppArea frontend
     */
    public function testSriJsIsRepositionedBeforeRequireJsConfig(): void
    {
        $assetCollection = $this->pageConfig->getAssetCollection();

        // Simulate requirejs/require.js being on the page (normally added via layout XML).
        // The RequireJs Config block uses it as the anchor for all its insert() calls.
        $requireJsAsset = $this->assetRepository->createAsset(RequireJsConfig::REQUIRE_JS_FILE_NAME);
        $assetCollection->add(RequireJsConfig::REQUIRE_JS_FILE_NAME, $requireJsAsset);

        // Simulate sri.js being added by layout XML before the block runs.
        $sriAsset = $this->assetRepository->createAsset(self::SRI_JS_ID);
        $assetCollection->add(self::SRI_JS_ID, $sriAsset);

        // createBlock() calls setLayout() → _prepareLayout() adds requirejs assets
        // → afterSetLayout plugin fires and repositions sri.js.
        $this->layout->createBlock(RequireJsBlock::class, 'test-requirejs-config');

        $allKeys = array_keys($assetCollection->getAll());

        $sriIndex = array_search(self::SRI_JS_ID, $allKeys, true);
        $this->assertNotFalse($sriIndex, 'sri.js must be present in the asset collection');

        $configIndex = null;
        foreach ($allKeys as $idx => $key) {
            if (str_ends_with($key, 'requirejs-config.js')) {
                $configIndex = $idx;
                break;
            }
        }

        $this->assertNotNull($configIndex, 'requirejs-config.js must be present in the asset collection');
        $this->assertSame(
            $configIndex - 1,
            $sriIndex,
            'sri.js must be immediately before requirejs-config.js'
        );
    }
}
