<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\WebapiAsync\Model\RouteCustomizationConfig\Reader;

/**
 * This class gives access to consolidated web API configuration from <Module_Name>/etc/route_customization.xml files.
 *
 * @api
 */
class RouteCustomizationConfig
{
    const CACHE_ID = 'webapi_async_route_customization_config';

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
    private $routeCustomizations;

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
     * Return route customizations loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getRouteCustomizations()
    {
        if (null === $this->routeCustomizations) {
            $routeCustomizations = $this->cache->load(self::CACHE_ID);
            if ($routeCustomizations && is_string($routeCustomizations)) {
                $this->routeCustomizations = $this->serializer->unserialize($routeCustomizations);
            } else {
                $this->routeCustomizations = $this->configReader->read();
                $this->cache->save($this->serializer->serialize($this->routeCustomizations), self::CACHE_ID);
            }
        }
        return $this->routeCustomizations;
    }
}
