<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use Magento\Csp\Model\SubresourceIntegrity;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;
use Magento\Csp\Model\SubresourceIntegrityRepository;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Plugin\AddIntegrityToAssetHtml;
use Magento\Deploy\Package\Package;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Escaper;
use Magento\Framework\View\Page\Config\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for AddIntegrityToAssetHtml plugin
 */
class AddIntegrityToAssetHtmlTest extends TestCase
{
    /**
     * @var State|MockObject
     */
    private State $state;

    /**
     * @var SubresourceIntegrityRepositoryPool|MockObject
     */
    private SubresourceIntegrityRepositoryPool $integrityRepositoryPool;

    /**
     * @var Http|MockObject
     */
    private Http $request;

    /**
     * @var SriEnabledActions|MockObject
     */
    private SriEnabledActions $action;

    /**
     * @var Escaper|MockObject
     */
    private Escaper $escaper;

    /**
     * @var AddIntegrityToAssetHtml
     */
    private AddIntegrityToAssetHtml $plugin;

    protected function setUp(): void
    {
        $this->state = $this->createMock(State::class);
        $this->integrityRepositoryPool = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->request = $this->createMock(Http::class);
        $this->action = $this->createMock(SriEnabledActions::class);
        $this->escaper = $this->createMock(Escaper::class);

        $this->plugin = new AddIntegrityToAssetHtml(
            $this->state,
            $this->integrityRepositoryPool,
            $this->request,
            $this->action,
            $this->escaper
        );
    }

    /**
     * Test that integrity is added to merged script tags on payment pages
     */
    public function testAfterRenderHeadAssetsAddsIntegrityToMergedFiles(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/version123/_cache/merged/abc123.min.js"></script>';
        $hash = 'sha256-abcdef123456';
        $escapedHash = 'sha256-abcdef123456';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('checkout_index_index')
            ->willReturn(true);

        $integrity = $this->createMock(SubresourceIntegrity::class);
        $integrity->expects($this->once())
            ->method('getHash')
            ->willReturn($hash);

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->once())
            ->method('getByPath')
            ->with('_cache/merged/abc123.min.js')
            ->willReturn($integrity);

        $this->integrityRepositoryPool->expects($this->once())
            ->method('get')
            ->with(Package::BASE_AREA)
            ->willReturn($repository);

        $this->escaper->expects($this->once())
            ->method('escapeHtmlAttr')
            ->with($hash)
            ->willReturn($escapedHash);

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertStringContainsString('integrity="sha256-abcdef123456"', $result);
        $this->assertStringContainsString('crossorigin="anonymous"', $result);
    }

    /**
     * Test that non-payment pages are skipped
     */
    public function testAfterRenderHeadAssetsSkipsNonPaymentPages(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/_cache/merged/abc123.min.js"></script>';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('catalog_product_view');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('catalog_product_view')
            ->willReturn(false);

        $this->integrityRepositoryPool->expects($this->never())
            ->method('get');

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertEquals($html, $result);
    }

    /**
     * Test that non-merged scripts are not modified
     */
    public function testAfterRenderHeadAssetsIgnoresNonMergedScripts(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/frontend/Magento/luma/en_US/Magento_Checkout/js/view/cart.js"></script>';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('checkout_index_index')
            ->willReturn(true);

        $this->integrityRepositoryPool->expects($this->never())
            ->method('get');

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertEquals($html, $result);
    }

    /**
     * Test that scripts with existing integrity are not modified
     */
    public function testAfterRenderHeadAssetsSkipsScriptsWithExistingIntegrity(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/_cache/merged/abc123.min.js" integrity="sha256-existing"></script>';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('checkout_index_index')
            ->willReturn(true);

        $this->integrityRepositoryPool->expects($this->never())
            ->method('get');

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertEquals($html, $result);
    }

    /**
     * Test fallback to area-specific repository when base area has no hash
     */
    public function testAfterRenderHeadAssetsFallsBackToAreaRepository(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/_cache/merged/abc123.min.js"></script>';
        $hash = 'sha256-fromarea';
        $escapedHash = 'sha256-fromarea';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('checkout_index_index')
            ->willReturn(true);

        $baseRepository = $this->createMock(SubresourceIntegrityRepository::class);
        $baseRepository->expects($this->once())
            ->method('getByPath')
            ->with('_cache/merged/abc123.min.js')
            ->willReturn(null);

        $integrity = $this->createMock(SubresourceIntegrity::class);
        $integrity->expects($this->once())
            ->method('getHash')
            ->willReturn($hash);

        $areaRepository = $this->createMock(SubresourceIntegrityRepository::class);
        $areaRepository->expects($this->once())
            ->method('getByPath')
            ->with('_cache/merged/abc123.min.js')
            ->willReturn($integrity);

        $this->integrityRepositoryPool->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [Package::BASE_AREA, $baseRepository],
                ['frontend', $areaRepository]
            ]);

        $this->state->expects($this->once())
            ->method('getAreaCode')
            ->willReturn('frontend');

        $this->escaper->expects($this->once())
            ->method('escapeHtmlAttr')
            ->with($hash)
            ->willReturn($escapedHash);

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertStringContainsString('integrity="sha256-fromarea"', $result);
    }

    /**
     * Test that exception during area code retrieval is handled
     */
    public function testAfterRenderHeadAssetsHandlesStateException(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/_cache/merged/abc123.min.js"></script>';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('checkout_index_index')
            ->willReturn(true);

        $baseRepository = $this->createMock(SubresourceIntegrityRepository::class);
        $baseRepository->expects($this->once())
            ->method('getByPath')
            ->willReturn(null);

        $this->integrityRepositoryPool->expects($this->once())
            ->method('get')
            ->with(Package::BASE_AREA)
            ->willReturn($baseRepository);

        $this->state->expects($this->once())
            ->method('getAreaCode')
            ->willThrowException(new \Exception('State error'));

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertEquals($html, $result);
    }

    /**
     * Test handling multiple merged scripts in one HTML block
     */
    public function testAfterRenderHeadAssetsHandlesMultipleMergedScripts(): void
    {
        $subject = $this->createMock(Renderer::class);
        $html = '<script src="/static/_cache/merged/abc123.min.js"></script>' .
                '<script src="/static/_cache/merged/def456.min.js"></script>';

        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_index_index');

        $this->action->expects($this->once())
            ->method('isPaymentPageAction')
            ->with('checkout_index_index')
            ->willReturn(true);

        $integrity1 = $this->createMock(SubresourceIntegrity::class);
        $integrity1->expects($this->once())
            ->method('getHash')
            ->willReturn('sha256-hash1');

        $integrity2 = $this->createMock(SubresourceIntegrity::class);
        $integrity2->expects($this->once())
            ->method('getHash')
            ->willReturn('sha256-hash2');

        $repository = $this->createMock(SubresourceIntegrityRepository::class);
        $repository->expects($this->exactly(2))
            ->method('getByPath')
            ->willReturnMap([
                ['_cache/merged/abc123.min.js', $integrity1],
                ['_cache/merged/def456.min.js', $integrity2]
            ]);

        $this->integrityRepositoryPool->expects($this->exactly(2))
            ->method('get')
            ->with(Package::BASE_AREA)
            ->willReturn($repository);

        $this->escaper->expects($this->exactly(2))
            ->method('escapeHtmlAttr')
            ->willReturnOnConsecutiveCalls('sha256-hash1', 'sha256-hash2');

        $result = $this->plugin->afterRenderHeadAssets($subject, $html);

        $this->assertStringContainsString('integrity="sha256-hash1"', $result);
        $this->assertStringContainsString('integrity="sha256-hash2"', $result);
    }
}
