<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Interception\Config;

/**
 * Interception cache manager.
 *
 * Responsible for handling interaction with compiled and uncompiled interception data
 */
class CacheManager
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigWriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled
     */
    private $compiledLoader;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\ObjectManager\ConfigWriterInterface $configWriter
     * @param \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled $compiledLoader
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\ObjectManager\ConfigWriterInterface $configWriter,
        \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled $compiledLoader
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->configWriter = $configWriter;
        $this->compiledLoader = $compiledLoader;
    }

    /**
     * Load the interception config from cache
     *
     * @param string $key
     * @return array|null
     */
    public function load(string $key): ?array
    {
        if ($this->isCompiled($key)) {
            return $this->compiledLoader->load($key);
        }

        $intercepted = $this->cache->load($key);
        return $intercepted ? $this->serializer->unserialize($intercepted) : null;
    }

    /**
     * Save config to cache backend
     *
     * @param string $key
     * @param array $data
     */
    public function save(string $key, array $data)
    {
        $this->cache->save($this->serializer->serialize($data), $key);
    }

    /**
     * Save config to filesystem
     *
     * @param string $key
     * @param array $data
     */
    public function saveCompiled(string $key, array $data)
    {
        // sort configuration to have it in the same order on every build
        ksort($data);

        $this->configWriter->write($key, $data);
    }

    /**
     * Purge interception cache
     *
     * @param string $key
     */
    public function clean(string $key)
    {
        $this->cache->remove($key);
    }

    /**
     * Check for the compiled config with the generated metadata
     *
     * @param string $key
     * @return bool
     */
    private function isCompiled(string $key): bool
    {
        return file_exists(\Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::getFilePath($key));
    }
}
