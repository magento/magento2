<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Plugin\PrependSriHashesToHeadAssets;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PrependSriHashesToHeadAssets plugin.
 */
class PrependSriHashesToHeadAssetsTest extends TestCase
{
    /** @var string */
    private const HASHES_BLOCK = 'csp.sri.hashes';

    /** @var string */
    private const HASHES_HTML = 'window.sriHashes = {"file.js":"sha256-abc"}';

    /** @var string */
    private const ASSETS_HTML = 'styles.css' . "\n" . 'require.js' . "\n" . 'requirejs-config.js';

    /** @var LayoutInterface|MockObject */
    private LayoutInterface $layout;

    /** @var Renderer|MockObject */
    private Renderer $renderer;

    /** @var PrependSriHashesToHeadAssets */
    private PrependSriHashesToHeadAssets $plugin;

    protected function setUp(): void
    {
        $this->layout = $this->createMock(LayoutInterface::class);
        $this->renderer = $this->createStub(Renderer::class);
        $this->plugin = new PrependSriHashesToHeadAssets($this->layout);
    }

    /**
     * Builds a head.additional mock with csp.sri.hashes as a child.
     *
     * @param AbstractBlock $hashesBlock
     * @return AbstractBlock|MockObject
     */
    private function buildHeadAdditional(AbstractBlock $hashesBlock): AbstractBlock
    {
        $headAdditional = $this->createMock(AbstractBlock::class);
        $headAdditional->method('getChildBlock')
            ->with(self::HASHES_BLOCK)
            ->willReturn($hashesBlock);
        return $headAdditional;
    }

    /**
     * Expected: hashes HTML prepended before assets, block removed from head.additional.
     */
    public function testExpectedHashesPrependedAndRemovedFromHeadAdditional(): void
    {
        $hashesBlock = $this->createMock(AbstractBlock::class);
        $hashesBlock->method('toHtml')->willReturn(self::HASHES_HTML);

        $headAdditional = $this->buildHeadAdditional($hashesBlock);
        $headAdditional->expects($this->once())
            ->method('unsetChild')
            ->with(self::HASHES_BLOCK);

        $this->layout->method('getBlock')->with('head.additional')->willReturn($headAdditional);

        $result = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);

        $this->assertSame(self::HASHES_HTML . self::ASSETS_HTML, $result);
    }

    /**
     * Expected: window.sriHashes appears before requirejs-config.js in the output.
     */
    public function testExpectedHashesPositionedBeforeRequireJsConfig(): void
    {
        $hashesBlock = $this->createMock(AbstractBlock::class);
        $hashesBlock->method('toHtml')->willReturn(self::HASHES_HTML);

        $this->layout->method('getBlock')
            ->with('head.additional')
            ->willReturn($this->buildHeadAdditional($hashesBlock));

        $result = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);

        $this->assertLessThan(
            strpos($result, 'requirejs-config.js'),
            strpos($result, 'sriHashes'),
            'window.sriHashes must appear before requirejs-config.js'
        );
    }

    /**
     * head.additional absent — result returned unchanged, no error thrown.
     */
    public function testHeadAdditionalAbsentReturnsOriginalResult(): void
    {
        $this->layout->method('getBlock')->with('head.additional')->willReturn(null);

        $result = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);

        $this->assertSame(self::ASSETS_HTML, $result);
    }

    /**
     * csp.sri.hashes not a child of head.additional — result returned unchanged, unsetChild never called.
     */
    public function testHashesBlockNotChildOfHeadAdditionalReturnsOriginalResult(): void
    {
        $headAdditional = $this->createMock(AbstractBlock::class);
        $headAdditional->method('getChildBlock')->with(self::HASHES_BLOCK)->willReturn(null);
        $headAdditional->expects($this->never())->method('unsetChild');

        $this->layout->method('getBlock')->with('head.additional')->willReturn($headAdditional);

        $result = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);

        $this->assertSame(self::ASSETS_HTML, $result);
    }

    /**
     * toHtml() returns empty string — unsetChild still called, result unchanged.
     */
    public function testEmptyHashesHtmlUnsetChildCalledResultUnchanged(): void
    {
        $hashesBlock = $this->createMock(AbstractBlock::class);
        $hashesBlock->method('toHtml')->willReturn('');

        $headAdditional = $this->buildHeadAdditional($hashesBlock);
        $headAdditional->expects($this->once())->method('unsetChild')->with(self::HASHES_BLOCK);

        $this->layout->method('getBlock')->with('head.additional')->willReturn($headAdditional);

        $result = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);

        $this->assertSame(self::ASSETS_HTML, $result);
    }

    /**
     * Second call after unsetChild has run returns original result — no double prepend.
     */
    public function testIdempotentOnSecondCall(): void
    {
        $hashesBlock = $this->createMock(AbstractBlock::class);
        $hashesBlock->method('toHtml')->willReturn(self::HASHES_HTML);

        $callCount = 0;
        $headAdditional = $this->createMock(AbstractBlock::class);
        $headAdditional->method('getChildBlock')
            ->willReturnCallback(function () use ($hashesBlock, &$callCount) {
                return $callCount++ === 0 ? $hashesBlock : null;
            });

        $this->layout->method('getBlock')->with('head.additional')->willReturn($headAdditional);

        $first = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);
        $second = $this->plugin->afterRenderHeadAssets($this->renderer, self::ASSETS_HTML);

        $this->assertSame(self::HASHES_HTML . self::ASSETS_HTML, $first);
        $this->assertSame(self::ASSETS_HTML, $second);
    }
}
