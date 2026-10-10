<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\Layout;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Cache\Backend\ExtendedBackendInterface;
use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;
use Magento\Framework\Cache\CompositeStaleCacheNotifier;
use Magento\Framework\Cache\StaleCacheNotifierInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Layout;
use Magento\PageCache\Model\Cache\Type as FullPageCacheType;
use Magento\PageCache\Model\Config;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for GitHub issue #40281.
 *
 * When use_stale_cache disables full_page mid-request after public headers were set,
 * LayoutPlugin must revoke public Cache-Control so Varnish does not store an unpurgeable page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 */
class LayoutPluginStaleCacheRaceTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StateInterface
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $originalFullPageState;

    /**
     * @var LayoutPlugin
     */
    private $layoutPlugin;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cacheState = $this->objectManager->get(StateInterface::class);
        $this->originalFullPageState = $this->cacheState->isEnabled(FullPageCacheType::TYPE_IDENTIFIER);
        $this->cacheState->setEnabled(FullPageCacheType::TYPE_IDENTIFIER, true);

        $this->response = $this->objectManager->get(HttpResponse::class);
        $this->config = $this->objectManager->get(Config::class);
        $this->layoutPlugin = $this->objectManager->create(LayoutPlugin::class, [
            'response' => $this->response,
            'config' => $this->config,
        ]);

        $this->response->clearHeaders();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if (isset($this->cacheState)) {
            $this->cacheState->setEnabled(FullPageCacheType::TYPE_IDENTIFIER, $this->originalFullPageState);
        }
        if (isset($this->response)) {
            $this->response->clearHeaders();
        }
    }

    /**
     * Happy path: when full page cache stays enabled for the whole request,
     * public headers and X-Magento-Tags are both set.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoConfigFixture default/system/full_page_cache/ttl 86400
     */
    public function testPublicHeadersAndTagsAreSetWhenFullPageCacheStaysEnabled(): void
    {
        $layout = $this->createCacheableLayoutWithIdentityBlock(['cat_p_1', 'cat_c_2']);

        $this->assertTrue($this->config->isEnabled());

        $this->layoutPlugin->afterGenerateElements($layout);
        $this->layoutPlugin->afterGetOutput($layout, 'html');

        $this->assertPublicCacheControl();
        $this->assertXMagentoTagsContain(['cat_p_1', 'cat_c_2']);
    }

    /**
     * Issue #40281 race:
     *
     * 1. afterGenerateElements sees FPC enabled → sets public Cache-Control
     * 2. Stale-cache notifier disables full_page for the rest of the request
     * 3. afterGetOutput sees FPC disabled → revokes public headers, does not set tags
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoConfigFixture default/system/full_page_cache/ttl 86400
     */
    public function testStaleCacheNotificationBetweenLayoutPluginsMustNotLeavePublicHeadersWithoutTags(): void
    {
        $layout = $this->createCacheableLayoutWithIdentityBlock(['cat_p_1', 'cat_c_2']);

        $this->assertTrue($this->config->isEnabled());
        $this->layoutPlugin->afterGenerateElements($layout);
        $this->assertPublicCacheControl('Public headers must be set while FPC is enabled');

        $this->objectManager->get(CompositeStaleCacheNotifier::class)
            ->cacheLoaderIsUsingStaleCache();

        $this->assertFalse(
            $this->config->isEnabled(),
            'Stale-cache notification must disable full_page for the current request'
        );

        $this->layoutPlugin->afterGetOutput($layout, 'html');

        $this->assertNotPublicCacheControl();
        $this->assertXMagentoTagsMissing();
    }

    /**
     * End-to-end chain: RemoteSynchronizedCache stale load → notifier → FPC off → no-cache headers.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoConfigFixture default/system/full_page_cache/ttl 86400
     */
    public function testRemoteSynchronizedStaleCacheLoadDisablesFullPageAndRevokesPublicHeaders(): void
    {
        $layout = $this->createCacheableLayoutWithIdentityBlock(['cat_p_1']);

        $this->layoutPlugin->afterGenerateElements($layout);
        $this->assertTrue($this->config->isEnabled());
        $this->assertPublicCacheControl();

        $backend = $this->createRemoteSynchronizedCacheBackendWithForcedStalePath();
        $loaded = $backend->load('some_cache_id');
        $this->assertSame(
            'stale-local-payload',
            $loaded,
            'Stale local payload must be returned when remote is empty and lock is held'
        );
        $this->assertFalse(
            $this->config->isEnabled(),
            'Full page cache must be disabled for the request after stale-cache notification'
        );

        $this->layoutPlugin->afterGetOutput($layout, 'html');

        $this->assertNotPublicCacheControl();
        $this->assertXMagentoTagsMissing();
    }

    /**
     * Proves RuntimeStaleCacheStateModifier is wired to disable only full_page.
     */
    public function testStaleCacheNotifierDisablesFullPageCacheTypeOnly(): void
    {
        $this->assertTrue($this->cacheState->isEnabled(FullPageCacheType::TYPE_IDENTIFIER));

        /** @var StaleCacheNotifierInterface $notifier */
        $notifier = $this->objectManager->get(CompositeStaleCacheNotifier::class);
        $notifier->cacheLoaderIsUsingStaleCache();

        $this->assertFalse($this->cacheState->isEnabled(FullPageCacheType::TYPE_IDENTIFIER));
        $this->assertFalse($this->config->isEnabled());
    }

    /**
     * @param string[] $identities
     * @return Layout&MockObject
     */
    private function createCacheableLayoutWithIdentityBlock(array $identities): Layout
    {
        $identityBlock = new class ($identities) implements IdentityInterface {
            /**
             * @param string[] $identities
             */
            public function __construct(private array $identities)
            {
            }

            /**
             * @inheritdoc
             */
            public function getIdentities()
            {
                return $this->identities;
            }

            public function getTtl(): int
            {
                return 0;
            }
        };

        /** @var Layout&MockObject $layout */
        $layout = $this->createStub(Layout::class);
        $layout->method('isCacheable')->willReturn(true);
        $layout->method('getAllBlocks')->willReturn([$identityBlock]);

        return $layout;
    }

    /**
     * Build RemoteSynchronizedCache that hits the stale-cache + failed-lock branch.
     */
    private function createRemoteSynchronizedCacheBackendWithForcedStalePath(): RemoteSynchronizedCache
    {
        /** @var ExtendedBackendInterface&MockObject $local */
        $local = $this->createStub(ExtendedBackendInterface::class);
        /** @var ExtendedBackendInterface&MockObject $remote */
        $remote = $this->createStub(ExtendedBackendInterface::class);

        $local->method('load')->willReturn('stale-local-payload');

        $remote->method('load')->willReturnCallback(
            static function (string $id) {
                if (str_ends_with($id, ':hash')) {
                    return false;
                }
                if (str_starts_with($id, 'rsl::')) {
                    return 'lock-held-by-another-process';
                }
                return false;
            }
        );

        return new RemoteSynchronizedCache(
            [
                'remote_backend' => $remote,
                'local_backend' => $local,
                'use_stale_cache' => true,
            ]
        );
    }

    private function assertPublicCacheControl(string $message = ''): void
    {
        $cacheControl = $this->getHeaderValue('cache-control');
        $this->assertNotNull($cacheControl, $message ?: 'cache-control header must be present');
        $this->assertStringContainsString(
            'public',
            strtolower((string)$cacheControl),
            $message ?: 'cache-control must be public for cacheable FPC pages'
        );
    }

    private function assertNotPublicCacheControl(): void
    {
        $cacheControl = $this->getHeaderValue('cache-control');
        $this->assertNotNull($cacheControl, 'cache-control header must be present after revoke');
        $this->assertStringNotContainsString(
            'public',
            strtolower((string)$cacheControl),
            'Public Cache-Control must be revoked when FPC is disabled mid-request'
        );
        $this->assertMatchesRegularExpression(
            '/no-store|no-cache/i',
            (string)$cacheControl,
            'Response must use no-cache headers so Varnish does not store the page'
        );

        // Full setNoCacheHeaders() contract (pragma + cache-control + expires).
        $pragma = $this->getHeaderValue('pragma');
        $this->assertNotNull($pragma, 'pragma header must be present after revoke');
        $this->assertStringContainsString(
            'no-cache',
            strtolower((string)$pragma),
            'pragma must be no-cache after public headers are revoked'
        );

        $expires = $this->getHeaderValue('expires');
        $this->assertNotNull($expires, 'expires header must be present after revoke');
        $expiresTimestamp = strtotime((string)$expires);
        $this->assertNotFalse($expiresTimestamp, 'expires header must be a parseable date');
        $this->assertLessThan(
            time(),
            $expiresTimestamp,
            'expires must be in the past after setNoCacheHeaders()'
        );
    }

    /**
     * @param string[] $expectedTags
     */
    private function assertXMagentoTagsContain(array $expectedTags): void
    {
        $header = $this->response->getHeader('X-Magento-Tags');
        $this->assertNotFalse($header, 'X-Magento-Tags header must be present');
        $actual = array_filter(explode(',', (string)$header->getFieldValue()));
        foreach ($expectedTags as $tag) {
            $this->assertContains($tag, $actual, "X-Magento-Tags must contain {$tag}");
        }
    }

    private function assertXMagentoTagsMissing(): void
    {
        $tagsHeader = $this->response->getHeader('X-Magento-Tags');
        $this->assertTrue(
            $tagsHeader === false
            || $tagsHeader === null
            || $tagsHeader->getFieldValue() === ''
            || $tagsHeader->getFieldValue() === null,
            'X-Magento-Tags must not be set when FPC is disabled mid-request'
        );
    }

    private function getHeaderValue(string $name): ?string
    {
        $header = $this->response->getHeader($name);
        if (!$header) {
            return null;
        }
        $value = $header->getFieldValue();
        return $value === false ? null : (string)$value;
    }
}
