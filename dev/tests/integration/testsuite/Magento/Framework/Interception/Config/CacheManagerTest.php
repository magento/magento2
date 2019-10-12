<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Interception\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class CacheManagerTest extends \PHPUnit\Framework\TestCase
{
    const CACHE_ID = 'interceptiontest';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigWriterInterface
     */
    private $configWriter;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->serializer = $this->objectManager->get(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->cache = $this->objectManager->get(\Magento\Framework\App\CacheInterface::class);
        $this->configWriter =
            $this->objectManager->get(\Magento\Framework\App\ObjectManager\ConfigWriter\Filesystem::class);

        $this->initializeMetadataDirectory();
    }

    /**
     * Delete compiled file if it was created and clear cache data
     */
    protected function tearDown()
    {
        $compiledPath = \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::getFilePath(self::CACHE_ID);
        if (file_exists($compiledPath)) {
            unlink($compiledPath);
        }

        $this->cache->remove(self::CACHE_ID);
    }

    /**
     * Test load interception cache from generated/metadata
     * @dataProvider interceptionCompiledConfigDataProvider
     * @param array $testConfig
     */
    public function testInstantiateFromCompiled(array $testConfig)
    {
        $this->configWriter->write(self::CACHE_ID, $testConfig);
        $config = $this->getConfig();

        $this->assertEquals($testConfig, $config->load(self::CACHE_ID));
    }

    /**
     * Test load interception cache from backend cache
     * @dataProvider interceptionCacheConfigDataProvider
     * @param array $testConfig
     */
    public function testInstantiateFromCache(array $testConfig)
    {
        $this->cache->save($this->serializer->serialize($testConfig), self::CACHE_ID);
        $config = $this->getConfig();

        $this->assertEquals($testConfig, $config->load(self::CACHE_ID));
    }

    public function interceptionCompiledConfigDataProvider()
    {
        return [
            [['classA' => true, 'classB' => false]],
            [['classA' => false, 'classB' => true]],
        ];
    }

    public function interceptionCacheConfigDataProvider()
    {
        return [
            [['classC' => true, 'classD' => false]],
            [['classC' => false, 'classD' => true]],
        ];
    }

    /**
     * Ensure generated/metadata exists
     */
    private function initializeMetadataDirectory()
    {
        $diPath = DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_METADATA][DirectoryList::PATH];
        $fullPath = BP . DIRECTORY_SEPARATOR . $diPath;
        if (!file_exists($fullPath)) {
            mkdir($fullPath);
        }
    }

    /**
     * Create instance of Config class with specific cacheId. This is done to prevent our test
     * from altering the interception config that may have been generated during application
     * installation. Inject a new instance of the compileLoaded to bypass it's caching.
     *
     * @return \Magento\Framework\Interception\Config\CacheManager
     */
    private function getConfig()
    {
        return $this->objectManager->create(
            \Magento\Framework\Interception\Config\CacheManager::class,
            [
                'cacheId' => self::CACHE_ID,
                'compiledLoader' => $this->objectManager->create(
                    \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::class
                ),
            ]
        );
    }
}
