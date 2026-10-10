<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Plugin;

use Magento\Csp\Block\Sri\Hashes;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Text\ListText;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for PrependSriHashesToHeadAssets plugin.
 *
 * Verifies the plugin is correctly wired via DI and that window.sriHashes
 * is moved from head.additional into the head assets output before
 * requirejs-config.js.
 *
 * @magentoAppIsolation enabled
 */
class PrependSriHashesToHeadAssetsTest extends TestCase
{
    /** @var string */
    private const HASHES_BLOCK = 'csp.sri.hashes';

    /** @var LayoutInterface */
    private LayoutInterface $layout;

    /** @var Renderer */
    private Renderer $renderer;

    /** @var PageConfig */
    private PageConfig $pageConfig;

    /** @var AssetRepository */
    private AssetRepository $assetRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->layout = $objectManager->get(LayoutInterface::class);
        $this->renderer = $objectManager->get(Renderer::class);
        $this->pageConfig = $objectManager->get(PageConfig::class);
        $this->assetRepository = $objectManager->get(AssetRepository::class);
    }

    /**
     * Plugin prepends window.sriHashes before assets and removes the block from head.additional.
     *
     * @magentoAppArea frontend
     */
    public function testExpectedHashesPrependedToAssetsAndRemovedFromHeadAdditional(): void
    {
        $headAdditional = $this->layout->createBlock(
            ListText::class,
            'head.additional'
        );

        $hashesBlock = $this->layout->createBlock(Hashes::class, self::HASHES_BLOCK);
        $hashesBlock->setTemplate('Magento_Csp::sri/hashes.phtml');
        $headAdditional->append($hashesBlock);

        $configAsset = $this->assetRepository->createArbitrary(
            'frontend/Magento/luma/en_US/requirejs-config.js',
            ''
        );
        $this->pageConfig->getAssetCollection()->add('requirejs-config.js', $configAsset);

        $assetsHtml = $this->renderer->renderHeadAssets();

        $this->assertStringContainsString(
            'window.sriHashes',
            $assetsHtml,
            'window.sriHashes must be present in head assets output'
        );
        $this->assertLessThan(
            strpos($assetsHtml, 'requirejs-config.js'),
            strpos($assetsHtml, 'sriHashes'),
            'window.sriHashes must appear before requirejs-config.js'
        );

        $headAdditionalHtml = $headAdditional->toHtml();
        $this->assertStringNotContainsString(
            'window.sriHashes',
            $headAdditionalHtml,
            'window.sriHashes must not render again in head.additional'
        );
    }

    /**
     * Plugin leaves assets output unchanged when csp.sri.hashes is not a child of head.additional.
     *
     * @magentoAppArea frontend
     */
    public function testExpectedAssetsUnchangedWhenHashesBlockAbsentFromHeadAdditional(): void
    {
        $this->layout->createBlock(
            ListText::class,
            'head.additional'
        );

        $configAsset = $this->assetRepository->createArbitrary(
            'frontend/Magento/luma/en_US/requirejs-config.js',
            ''
        );
        $this->pageConfig->getAssetCollection()->add('requirejs-config.js', $configAsset);

        $assetsHtml = $this->renderer->renderHeadAssets();

        $this->assertStringNotContainsString(
            'window.sriHashes',
            $assetsHtml,
            'window.sriHashes must not appear on pages without csp.sri.hashes block'
        );
    }

    /**
     * Plugin renders window.sriHashes exactly once; subsequent renderHeadAssets() calls omit it.
     *
     * @magentoAppArea frontend
     */
    public function testExpectedHashesRenderedOnceOnlyAcrossMultipleRenderCalls(): void
    {
        $headAdditional = $this->layout->createBlock(
            ListText::class,
            'head.additional'
        );
        $hashesBlock = $this->layout->createBlock(Hashes::class, self::HASHES_BLOCK);
        $hashesBlock->setTemplate('Magento_Csp::sri/hashes.phtml');
        $headAdditional->append($hashesBlock);

        $configAsset = $this->assetRepository->createArbitrary(
            'frontend/Magento/luma/en_US/requirejs-config.js',
            ''
        );
        $this->pageConfig->getAssetCollection()->add('requirejs-config.js', $configAsset);

        $first = $this->renderer->renderHeadAssets();
        $second = $this->renderer->renderHeadAssets();

        $this->assertSame(
            substr_count($first, 'window.sriHashes'),
            1,
            'window.sriHashes must appear exactly once in first call'
        );
        $this->assertStringNotContainsString(
            'window.sriHashes',
            $second,
            'window.sriHashes must not appear in subsequent call'
        );
    }
}
