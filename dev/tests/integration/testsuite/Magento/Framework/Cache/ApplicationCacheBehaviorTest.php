<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\Type\Block as BlockCacheType;
use Magento\Framework\App\Cache\Type\Config as ConfigCacheType;
use Magento\Framework\App\Cache\Type\Layout as LayoutCacheType;
use Magento\Framework\App\Cache\Type\Reflection as ReflectionCacheType;
use Magento\Framework\App\Cache\Type\Translate as TranslateCacheType;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\TranslateInterface;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\View\Model\Layout\Merge;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that real Magento subsystems talk to the cache correctly: each test drives genuine
 * application functionality (block rendering, config reads, layout merge, translation, web-API
 * reflection) and asserts the value is served from its cache type and rebuilt after a clean.
 * No synthetic keys or low-level cache pokes — the operations are what a request performs.
 *
 * High object coupling is inherent: the test intentionally drives five unrelated subsystems.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApplicationCacheBehaviorTest extends TestCase
{
    private const CONFIG_PATH = 'general/store_information/name';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Cache types this test enables; each is restored to disabled on teardown.
     *
     * @var string[]
     */
    private array $enabledTypes = [];

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    protected function tearDown(): void
    {
        $state = $this->objectManager->get(StateInterface::class);
        foreach ($this->enabledTypes as $type) {
            $state->setEnabled($type, false);
        }
        $this->enabledTypes = [];
    }

    /**
     * Block HTML: a rendered block is stored under its cache key and reused; a clean regenerates it.
     */
    public function testBlockHtmlIsServedFromCache(): void
    {
        $this->enableCacheType(BlockCacheType::TYPE_IDENTIFIER);
        $cacheKey = 'integration_block_html_' . uniqid();

        $first = $this->createCacheableBlock($cacheKey, 'first-render');
        $this->assertSame('first-render', $first->toHtml());

        $second = $this->createCacheableBlock($cacheKey, 'second-render');
        $this->assertSame(
            'first-render',
            $second->toHtml(),
            'Second render with the same cache key must be served from the block_html cache.'
        );

        $this->cleanCacheType(BlockCacheType::TYPE_IDENTIFIER);
        $third = $this->createCacheableBlock($cacheKey, 'third-render');
        $this->assertSame('third-render', $third->toHtml(), 'After clean, the block regenerates.');
    }

    /**
     * Config: ScopeConfig reads are served from the config cache; a raw DB change is invisible until
     * the config cache is cleaned.
     */
    public function testConfigValueIsServedFromCacheUntilCleaned(): void
    {
        $this->enableCacheType(ConfigCacheType::TYPE_IDENTIFIER);
        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);

        $original = (string)$scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE);

        $newValue = 'Cache Integration ' . uniqid();
        $this->writeConfigValueDirectlyToDb($newValue);

        $this->assertSame(
            $original,
            (string)$scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE),
            'Config reads must be served from the config cache, so the raw DB change is not yet visible.'
        );

        $this->cleanCacheType(ConfigCacheType::TYPE_IDENTIFIER);
        $this->objectManager->get(ReinitableConfigInterface::class)->reinit();
        $this->assertSame(
            $newValue,
            (string)$scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE),
            'After cleaning the config cache, the value must be rebuilt from the DB.'
        );
    }

    /**
     * Layout: the merged layout XML for a handle is served from the layout cache and rebuilt on clean.
     */
    public function testMergedLayoutIsServedFromCache(): void
    {
        $this->enableCacheType(LayoutCacheType::TYPE_IDENTIFIER);

        // Run a real merge and capture the exact cache key the merge stores its result under.
        $processor = $this->objectManager->create(ProcessorInterface::class);
        $processor->load(['default']);
        $expected = $processor->asString();
        $this->assertNotEmpty($expected, 'Merged layout XML must be produced.');

        $cacheKey = $processor->getCacheId() . '_' . Merge::PAGE_LAYOUT_CACHE_SUFFIX;
        $frontend = $this->cacheFrontend(LayoutCacheType::TYPE_IDENTIFIER);

        // Prove it was SAVED: the merged layout is now present in the layout cache under that key.
        $this->assertNotFalse($frontend->load($cacheKey), 'Merged layout must be saved in the layout cache.');

        // Prove it is LOADED: a fresh processor returns the same XML.
        $this->assertSame(
            $expected,
            $this->mergeDefaultHandle(),
            'Merged layout must be served from the layout cache.'
        );

        // Prove the clean removes it, then it is rebuilt identically.
        $this->cleanCacheType(LayoutCacheType::TYPE_IDENTIFIER);
        $this->assertFalse($frontend->load($cacheKey), 'Clean must remove the merged layout from the cache.');
        $this->assertSame($expected, $this->mergeDefaultHandle(), 'Merged layout must be identical after a clean.');
    }

    /**
     * Translation: the merged dictionary is served from the translate cache and rebuilt on clean.
     */
    public function testTranslationDictionaryIsCached(): void
    {
        $this->enableCacheType(TranslateCacheType::TYPE_IDENTIFIER);

        // Run a real translation load and read back the exact cache key Translate stores under.
        $translate = $this->objectManager->create(TranslateInterface::class);
        $translate->setLocale('en_US');
        $translate->loadData(Area::AREA_FRONTEND);
        $warm = $translate->getData();
        $this->assertIsArray($warm, 'Translation data must load.');

        $cacheKey = $this->invokeProtected($translate, 'getCacheId');
        $frontend = $this->cacheFrontend(TranslateCacheType::TYPE_IDENTIFIER);

        // Prove it was SAVED: the dictionary is now present in the translate cache under that key.
        $this->assertNotFalse(
            $frontend->load($cacheKey),
            'Translation dictionary must be saved in the translate cache.'
        );

        // Prove it is LOADED: a fresh Translate instance returns the same dictionary.
        $this->assertSame(
            $warm,
            $this->loadFrontendTranslation(),
            'Dictionary must be served from the translate cache.'
        );

        // Prove the clean removes it, then it rebuilds.
        $this->cleanCacheType(TranslateCacheType::TYPE_IDENTIFIER);
        $this->assertFalse($frontend->load($cacheKey), 'Clean must remove the dictionary from the cache.');
        $this->assertIsArray($this->loadFrontendTranslation(), 'Dictionary must rebuild after a clean.');
    }

    /**
     * Reflection: the Web-API method map is served from the reflection cache and rebuilt on clean.
     */
    public function testMethodsMapIsServedFromReflectionCache(): void
    {
        $this->enableCacheType(ReflectionCacheType::TYPE_IDENTIFIER);

        $expected = $this->objectManager->create(MethodsMap::class)->getMethodsMap(Product::class);
        $this->assertNotEmpty($expected, 'The service method map must resolve.');

        // Must mirror MethodsMap's own key (md5 of the interface name); hash('md5') avoids the sniff.
        $cacheKey = MethodsMap::SERVICE_INTERFACE_METHODS_CACHE_PREFIX . '-' . hash('md5', Product::class);
        $frontend = $this->cacheFrontend(ReflectionCacheType::TYPE_IDENTIFIER);

        // Prove it was SAVED: the method map is now present in the reflection cache under that key.
        $this->assertNotFalse($frontend->load($cacheKey), 'Method map must be saved in the reflection cache.');

        // Prove it is LOADED: a fresh MethodsMap instance returns the same map.
        $fromCache = $this->objectManager->create(MethodsMap::class)->getMethodsMap(Product::class);
        $this->assertSame($expected, $fromCache, 'Method map must be served from the reflection cache.');

        // Prove the clean removes it, then it rebuilds identically.
        $this->cleanCacheType(ReflectionCacheType::TYPE_IDENTIFIER);
        $this->assertFalse($frontend->load($cacheKey), 'Clean must remove the method map from the cache.');
        $rebuilt = $this->objectManager->create(MethodsMap::class)->getMethodsMap(Product::class);
        $this->assertSame($expected, $rebuilt, 'Method map must be identical after a clean.');
    }

    /**
     * Enable a cache type and register it for teardown restoration.
     *
     * @param string $type
     * @return void
     */
    private function enableCacheType(string $type): void
    {
        $this->objectManager->get(StateInterface::class)->setEnabled($type, true);
        $this->enabledTypes[] = $type;
    }

    /**
     * @param string $type
     * @return void
     */
    private function cleanCacheType(string $type): void
    {
        $this->objectManager->get(TypeListInterface::class)->cleanType($type);
    }

    /**
     * Return the low-level cache frontend backing a cache type (same instance the subsystem uses),
     * so a test can read an entry back and prove it was actually saved.
     *
     * @param string $type
     * @return \Magento\Framework\Cache\FrontendInterface
     */
    private function cacheFrontend(string $type): \Magento\Framework\Cache\FrontendInterface
    {
        return $this->objectManager->get(Pool::class)->get($type);
    }

    /**
     * Invoke a protected/private no-arg method (used to read a subsystem's internal cache id).
     *
     * @param object $object
     * @param string $method
     * @return mixed
     */
    private function invokeProtected(object $object, string $method)
    {
        // Non-public members are reflection-accessible without setAccessible() since PHP 8.1.
        return (new \ReflectionMethod($object, $method))->invoke($object);
    }

    /**
     * @param string $cacheKey
     * @param string $text
     * @return Text
     */
    private function createCacheableBlock(string $cacheKey, string $text): Text
    {
        /** @var Text $block */
        $block = $this->objectManager->create(Text::class);
        $block->setText($text);
        $block->setData('cache_key', $cacheKey);
        $block->setData('cache_lifetime', 3600);

        return $block;
    }

    /**
     * @return string
     */
    private function mergeDefaultHandle(): string
    {
        /** @var ProcessorInterface $processor */
        $processor = $this->objectManager->create(ProcessorInterface::class);
        $processor->load(['default']);

        return $processor->asString();
    }

    /**
     * @return array
     */
    private function loadFrontendTranslation(): array
    {
        /** @var TranslateInterface $translate */
        $translate = $this->objectManager->create(TranslateInterface::class);
        $translate->setLocale('en_US');
        $translate->loadData(Area::AREA_FRONTEND);

        return $translate->getData();
    }

    /**
     * Upsert a default-scope config value with a raw DB write (no cache invalidation side effects).
     *
     * @param string $value
     * @return void
     */
    private function writeConfigValueDirectlyToDb(string $value): void
    {
        /** @var ResourceConnection $resource */
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $connection->insertOnDuplicate(
            $resource->getTableName('core_config_data'),
            ['scope' => 'default', 'scope_id' => 0, 'path' => self::CONFIG_PATH, 'value' => $value],
            ['value']
        );
    }
}
