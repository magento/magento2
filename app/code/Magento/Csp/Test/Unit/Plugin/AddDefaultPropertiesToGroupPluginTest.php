<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Csp\Plugin\AddDefaultPropertiesToGroupPlugin;
use Magento\Csp\Model\SubresourceIntegrityRepositoryPool;
use Magento\Csp\Model\SubresourceIntegrity\HashResolver\HashResolverInterface;
use Magento\Csp\Model\SubresourceIntegrity\SriEnabledActions;
use Magento\Framework\App\State;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Asset\ConfigInterface as AssetConfig;
use Magento\Framework\View\Asset\GroupedCollection;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\AssetInterface;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Unit tests for AddDefaultPropertiesToGroupPlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddDefaultPropertiesToGroupPluginTest extends TestCase
{
    /**
     * @var AddDefaultPropertiesToGroupPlugin
     */
    private AddDefaultPropertiesToGroupPlugin $plugin;

    /**
     * @var MockObject|State
     */
    private MockObject $stateMock;

    /**
     * @var MockObject|SubresourceIntegrityRepositoryPool
     */
    private MockObject $repositoryPoolMock;

    /**
     * @var MockObject|HashResolverInterface
     */
    private MockObject $hashResolverMock;

    /**
     * @var MockObject|Http
     */
    private MockObject $requestMock;

    /**
     * @var MockObject|SriEnabledActions
     */
    private MockObject $actionMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private MockObject $loggerMock;

    /**
     * @var MockObject|AssetConfig
     */
    private MockObject $assetConfigMock;

    protected function setUp(): void
    {
        $this->stateMock = $this->createMock(State::class);
        $this->repositoryPoolMock = $this->createMock(SubresourceIntegrityRepositoryPool::class);
        $this->hashResolverMock = $this->createMock(HashResolverInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->actionMock = $this->createMock(SriEnabledActions::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->assetConfigMock = $this->createMock(AssetConfig::class);
        $this->assetConfigMock->method('isMergeJsFiles')->willReturn(false);

        $this->plugin = new AddDefaultPropertiesToGroupPlugin(
            $this->stateMock,
            $this->repositoryPoolMock,
            $this->requestMock,
            $this->actionMock,
            $this->hashResolverMock,
            $this->loggerMock,
            $this->assetConfigMock
        );
    }

    /**
     * Test integrity and crossorigin are added when hash is found
     */
    public function testBeforeGetFilteredPropertiesAddsIntegrity(): void
    {
        $this->requestMock->method('getFullActionName')->willReturn('checkout_index_index');
        $this->actionMock->method('isPaymentPageAction')->willReturn(true);

        $assetMock = $this->createMock(LocalInterface::class);
        $assetMock->method('getPath')->willReturn('frontend/Magento/luma/en_US/js/file.js');

        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->expects($this->once())
            ->method('getHashByPath')
            ->with('frontend/Magento/luma/en_US/js/file.js')
            ->willReturn('sha256-test123');

        $result = $this->plugin->beforeGetFilteredProperties($subjectMock, $assetMock, []);

        $this->assertEquals('sha256-test123', $result[1]['attributes']['integrity']);
        $this->assertEquals('anonymous', $result[1]['attributes']['crossorigin']);
    }

    /**
     * Test non-payment pages are skipped
     */
    public function testBeforeGetFilteredPropertiesSkipsNonPaymentPages(): void
    {
        $this->requestMock->method('getFullActionName')->willReturn('catalog_product_view');
        $this->actionMock->method('isPaymentPageAction')->willReturn(false);

        $assetMock = $this->createMock(LocalInterface::class);
        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->expects($this->never())->method('getHashByPath');

        $result = $this->plugin->beforeGetFilteredProperties($subjectMock, $assetMock, []);

        $this->assertArrayNotHasKey('attributes', $result[1]);
    }

    /**
     * Test when hash is not found
     */
    public function testBeforeGetFilteredPropertiesNoHashFound(): void
    {
        $this->requestMock->method('getFullActionName')->willReturn('checkout_index_index');
        $this->actionMock->method('isPaymentPageAction')->willReturn(true);

        $assetMock = $this->createMock(LocalInterface::class);
        $assetMock->method('getPath')->willReturn('unknown/file.js');

        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->method('getHashByPath')->willReturn(null);

        $result = $this->plugin->beforeGetFilteredProperties($subjectMock, $assetMock, []);

        $this->assertArrayNotHasKey('attributes', $result[1]);
    }

    /**
     * Test existing properties are preserved
     */
    public function testBeforeGetFilteredPropertiesPreservesExistingProperties(): void
    {
        $this->requestMock->method('getFullActionName')->willReturn('checkout_index_index');
        $this->actionMock->method('isPaymentPageAction')->willReturn(true);

        $assetMock = $this->createMock(LocalInterface::class);
        $assetMock->method('getPath')->willReturn('file.js');

        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->method('getHashByPath')->willReturn('sha256-test');

        $existingProperties = ['async' => true, 'defer' => true];
        $result = $this->plugin->beforeGetFilteredProperties($subjectMock, $assetMock, $existingProperties);

        $this->assertTrue($result[1]['async']);
        $this->assertTrue($result[1]['defer']);
        $this->assertEquals('sha256-test', $result[1]['attributes']['integrity']);
    }

    /**
     * Test non-local assets are skipped
     */
    public function testBeforeGetFilteredPropertiesSkipsNonLocalAssets(): void
    {
        $this->requestMock->method('getFullActionName')->willReturn('checkout_index_index');
        $this->actionMock->method('isPaymentPageAction')->willReturn(true);

        $assetMock = $this->createMock(AssetInterface::class);
        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->expects($this->never())->method('getHashByPath');

        $result = $this->plugin->beforeGetFilteredProperties($subjectMock, $assetMock, []);

        $this->assertArrayNotHasKey('attributes', $result[1]);
    }

    /**
     * Test plugin skips adding integrity when JS merging is enabled
     */
    public function testBeforeGetFilteredPropertiesSkipsWhenMergingEnabled(): void
    {
        $assetConfigMergingOn = $this->createMock(AssetConfig::class);
        $assetConfigMergingOn->method('isMergeJsFiles')->willReturn(true);

        $plugin = new AddDefaultPropertiesToGroupPlugin(
            $this->stateMock,
            $this->repositoryPoolMock,
            $this->requestMock,
            $this->actionMock,
            $this->hashResolverMock,
            $this->loggerMock,
            $assetConfigMergingOn
        );

        $this->requestMock->method('getFullActionName')->willReturn('checkout_index_index');
        $this->actionMock->method('isPaymentPageAction')->willReturn(true);

        $assetMock = $this->createMock(LocalInterface::class);
        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->expects($this->never())->method('getHashByPath');

        $result = $plugin->beforeGetFilteredProperties($subjectMock, $assetMock, []);

        $this->assertArrayNotHasKey('attributes', $result[1]);
    }

    /**
     * Test exception handling - logs warning and continues
     */
    public function testBeforeGetFilteredPropertiesHandlesException(): void
    {
        $this->requestMock->method('getFullActionName')->willReturn('checkout_index_index');
        $this->actionMock->method('isPaymentPageAction')->willReturn(true);

        $assetMock = $this->createMock(LocalInterface::class);
        $assetMock->method('getPath')->willReturn('file.js');

        $subjectMock = $this->createMock(GroupedCollection::class);

        $this->hashResolverMock->method('getHashByPath')
            ->willThrowException(new Exception('Test exception'));

        $this->loggerMock->expects($this->once())
            ->method('warning')
            ->with(
                'SRI: Failed to get integrity hash for asset',
                $this->callback(function ($context) {
                    return isset($context['asset_path']) && isset($context['exception']);
                })
            );

        $result = $this->plugin->beforeGetFilteredProperties($subjectMock, $assetMock, []);

        $this->assertArrayNotHasKey('attributes', $result[1]);
    }
}
