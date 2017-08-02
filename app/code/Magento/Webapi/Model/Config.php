<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model;

use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\Webapi\Model\Config\Reader;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * This class gives access to consolidated web API configuration from <Module_Name>/etc/webapi.xml files.
 *
 * @api
 * @since 2.0.0
 */
class Config
{
    const CACHE_ID = 'webapi_config';

    /**
     * Pattern for Web API interface name.
     */
    const SERVICE_CLASS_PATTERN = '/^(.+?)\\\\(.+?)\\\\Service\\\\(V\d+)+(\\\\.+)Interface$/';

    const API_PATTERN = '/^(.+?)\\\\(.+?)\\\\Api(\\\\.+)Interface$/';

    /**
     * @var WebapiCache
     * @since 2.0.0
     */
    protected $cache;

    /**
     * @var Reader
     * @since 2.0.0
     */
    protected $configReader;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $services;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param Reader $configReader
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        WebapiCache $cache,
        Reader $configReader,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->configReader = $configReader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     * @since 2.0.0
     */
    public function getServices()
    {
        if (null === $this->services) {
            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->services = $this->serializer->unserialize($services);
            } else {
                $this->services = $this->configReader->read();
                $this->cache->save($this->serializer->serialize($this->services), self::CACHE_ID);
            }
        }
        return $this->services;
    }
}
