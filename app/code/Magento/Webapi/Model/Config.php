<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Webapi\Model;

use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\Webapi\Model\Config\Reader;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader\Compiled;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * This class gives access to consolidated web API configuration from <Module_Name>/etc/webapi.xml files.
 *
 * @api
 * @since 100.0.2
 */
class Config implements ConfigInterface
{
    const CACHE_ID = 'webapi_config';

    /**
     * Pattern for Web API interface name.
     */
    const SERVICE_CLASS_PATTERN = '/^(.+?)\\\\(.+?)\\\\Service\\\\(V\d+)+(\\\\.+)Interface$/';

    const API_PATTERN = '/^(.+?)\\\\(.+?)\\\\Api(\\\\.+)Interface$/';

    /**
     * @var WebapiCache
     */
    protected $cache;

    /**
     * @var Reader
     */
    protected $configReader;

    /**
     * @var array
     */
    protected $services;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ConfigWriterInterface|null
     */
    private $configWriter;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param Reader $configReader
     * @param SerializerInterface|null $serializer
     * @param ConfigWriterInterface|null $configWriter
     */
    public function __construct(
        WebapiCache $cache,
        Reader $configReader,
        ?SerializerInterface $serializer = null,
        ?ConfigWriterInterface $configWriter = null
    ) {
        $this->cache = $cache;
        $this->configReader = $configReader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        if (null === $this->services) {
            if ($this->configWriter && $this->isCompiledConfigAvailable(self::CACHE_ID)) {
                $this->services = $this->loadCompiledConfig(self::CACHE_ID);
                return $this->services;
            }

            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->services = $this->serializer->unserialize($services);
            } else {
                $this->services = $this->configReader->read();
                $this->cache->save($this->serializer->serialize($this->services), self::CACHE_ID);
            }
            $this->writeCompiledConfig(self::CACHE_ID, $this->services);
        }
        return $this->services;
    }

    /**
     * Check whether compiled config file exists
     *
     * @param string $key
     * @return bool
     */
    protected function isCompiledConfigAvailable(string $key): bool
    {
        return file_exists(Compiled::getFilePath($key));
    }

    /**
     * Load compiled config from file
     *
     * @param string $key
     * @return array
     */
    protected function loadCompiledConfig(string $key): array
    {
        return include Compiled::getFilePath($key);
    }

    /**
     * Remove compiled config file
     *
     * @param string $key
     * @return void
     */
    protected function removeCompiledConfig(string $key): void
    {
        $filePath = Compiled::getFilePath($key);
        if ($this->configWriter !== null && file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Write compiled config to file
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    private function writeCompiledConfig(string $key, array $data): void
    {
        if ($this->configWriter !== null) {
            $this->configWriter->write($key, $data);
        }
    }
}
