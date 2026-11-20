<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Plugin\RepositionSriBeforeRequireJsConfig;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\RequireJs\Block\Html\Head\Config as RequireJsBlock;
use Magento\RequireJs\Model\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for RepositionSriBeforeRequireJsConfig plugin.
 */
class RepositionSriBeforeRequireJsConfigTest extends TestCase
{
    private const SRI_JS_ID = 'Magento_Csp::js/sri.js';
    private const REQUIREJS_CONFIG_KEY = 'frontend/Magento/luma/en_US/requirejs-config.js';

    /** @var PageConfig|MockObject */
    private PageConfig $pageConfig;

    /** @var FileManager|MockObject */
    private FileManager $fileManager;

    /** @var GroupedCollection|MockObject */
    private GroupedCollection $assetCollection;

    /** @var RequireJsBlock|MockObject */
    private RequireJsBlock $subject;

    /** @var RepositionSriBeforeRequireJsConfig */
    private RepositionSriBeforeRequireJsConfig $plugin;

    protected function setUp(): void
    {
        $this->pageConfig      = $this->createStub(PageConfig::class);
        $this->fileManager     = $this->createStub(FileManager::class);
        $this->assetCollection = $this->createMock(GroupedCollection::class);
        $this->subject         = $this->createStub(RequireJsBlock::class);

        $this->pageConfig->method('getAssetCollection')->willReturn($this->assetCollection);

        $configAsset = $this->createStub(LocalInterface::class);
        $configAsset->method('getFilePath')->willReturn(self::REQUIREJS_CONFIG_KEY);
        $this->fileManager->method('createRequireJsConfigAsset')->willReturn($configAsset);

        $this->plugin = new RepositionSriBeforeRequireJsConfig(
            $this->pageConfig,
            $this->fileManager
        );
    }

    /**
     * Happy path: sri.js is after requirejs-config.js and gets repositioned before it.
     */
    public function testSriJsIsRepositionedBeforeRequireJsConfig(): void
    {
        $sriAsset = $this->createStub(LocalInterface::class);

        $this->assetCollection->method('has')->with(self::SRI_JS_ID)->willReturn(true);
        $this->assetCollection->method('getAll')->willReturn([
            'requirejs/require.js'   => $this->createStub(LocalInterface::class),
            'min-resolver.js'        => $this->createStub(LocalInterface::class),
            self::REQUIREJS_CONFIG_KEY => $this->createStub(LocalInterface::class),
            self::SRI_JS_ID          => $sriAsset,
        ]);

        $this->assetCollection->expects($this->once())->method('remove')->with(self::SRI_JS_ID);

        $insertCalls = [];
        $this->assetCollection->method('insert')
            ->willReturnCallback(function () use (&$insertCalls) {
                $args = func_get_args();
                $insertCalls[] = ['id' => $args[0], 'after' => $args[2]];
                return true;
            });

        $this->plugin->afterSetLayout($this->subject, $this->subject);

        $sriInsert = array_values(array_filter($insertCalls, fn($c) => $c['id'] === self::SRI_JS_ID));
        $this->assertNotEmpty($sriInsert, 'sri.js must be re-inserted');
        $this->assertSame('min-resolver.js', $sriInsert[0]['after']);
    }

    /**
     * sri.js is already directly before requirejs-config.js — no move needed.
     */
    public function testSriJsAlreadyCorrectlyPositionedIsNotMoved(): void
    {
        $this->assetCollection->method('has')->with(self::SRI_JS_ID)->willReturn(true);
        $this->assetCollection->method('getAll')->willReturn([
            'requirejs/require.js'   => $this->createStub(LocalInterface::class),
            self::SRI_JS_ID          => $this->createStub(LocalInterface::class),
            self::REQUIREJS_CONFIG_KEY => $this->createStub(LocalInterface::class),
        ]);

        $this->assetCollection->expects($this->never())->method('remove');
        $this->assetCollection->expects($this->never())->method('insert');

        $this->plugin->afterSetLayout($this->subject, $this->subject);
    }

    /**
     * sri.js is not in the collection — asset collection is not touched.
     */
    public function testSriJsAbsentDoesNotModifyCollection(): void
    {
        $this->assetCollection->method('has')->with(self::SRI_JS_ID)->willReturn(false);

        $this->assetCollection->expects($this->never())->method('getAll');
        $this->assetCollection->expects($this->never())->method('remove');
        $this->assetCollection->expects($this->never())->method('insert');

        $this->plugin->afterSetLayout($this->subject, $this->subject);
    }

    /**
     * requirejs-config.js is not in the collection — asset collection is not modified.
     */
    public function testRequireJsConfigAbsentDoesNotModifyCollection(): void
    {
        $this->assetCollection->method('has')->with(self::SRI_JS_ID)->willReturn(true);
        $this->assetCollection->method('getAll')->willReturn([
            'some-other-asset.js' => $this->createStub(LocalInterface::class),
            self::SRI_JS_ID       => $this->createStub(LocalInterface::class),
        ]);

        $this->assetCollection->expects($this->never())->method('remove');
        $this->assetCollection->expects($this->never())->method('insert');

        $this->plugin->afterSetLayout($this->subject, $this->subject);
    }

    /**
     * requirejs-config.js is at index 0 — nothing can be inserted before it.
     */
    public function testRequireJsConfigAtIndexZeroDoesNotModifyCollection(): void
    {
        $this->assetCollection->method('has')->with(self::SRI_JS_ID)->willReturn(true);
        $this->assetCollection->method('getAll')->willReturn([
            self::REQUIREJS_CONFIG_KEY => $this->createStub(LocalInterface::class),
            self::SRI_JS_ID            => $this->createStub(LocalInterface::class),
        ]);

        $this->assetCollection->expects($this->never())->method('remove');
        $this->assetCollection->expects($this->never())->method('insert');

        $this->plugin->afterSetLayout($this->subject, $this->subject);
    }
}
