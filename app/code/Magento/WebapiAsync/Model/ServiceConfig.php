<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;
use Magento\WebapiAsync\Model\ServiceConfig\Reader;

/**
 * This class gives access to consolidated web API configuration from <Module_Name>/etc/webapi_async.xml files.
 *
 * @api
 * @since 100.2.0
 */
class ServiceConfig
{
    const CACHE_ID = 'webapi_async_service_config';

    /**
     * @var WebapiCache
     */
    private $cache;

    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @var array
     */
    private $services;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param Reader $configReader
     * @param SerializerInterface $serializer
     */
    public function __construct(
        WebapiCache $cache,
        Reader $configReader,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->configReader = $configReader;
        $this->serializer = $serializer;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     * @since 100.2.0
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
