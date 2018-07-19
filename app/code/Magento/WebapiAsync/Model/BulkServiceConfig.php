<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Webapi\Model\Cache\Type\Webapi as WebapiCache;
use Magento\Webapi\Model\Config\Converter as WebapiConverter;
use Magento\Webapi\Model\Config;

/**
 * @api
 */
class BulkServiceConfig implements \Magento\Webapi\Model\ConfigInterface
{
    const CACHE_ID = 'webapi_bulk_async_service_config';
    const URL_PARAM_PREFIX_PLACEHOLDER = 'by';
    /**
     * @var WebapiCache
     */
    private $cache;
    /**
     * @var Config
     */
    private $webapiConfig;
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
     * @param Config $webapiConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        WebapiCache $cache,
        Config $webapiConfig,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->webapiConfig = $webapiConfig;
        $this->serializer = $serializer;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->services) {
            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->services = $this->serializer->unserialize($services);
            } else {
                $this->services = $this->getBulkServicesConfig();
                $this->cache->save($this->serializer->serialize($this->services), self::CACHE_ID);
            }
        }

        return $this->services;
    }

    /**
     * @return array
     */
    private function getBulkServicesConfig()
    {
        $bulkServices = [];
        $webapiServices = $this->webapiConfig->getServices();
        foreach ($webapiServices[WebapiConverter::KEY_ROUTES] as $routePath => $routeConfig) {
            foreach ($routeConfig as $httpMethod => $httpMethodConfig) {
                if ($httpMethod !== \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET) {
                    $routePath = preg_replace_callback(
                        '/\/:(\w+)/',
                        function ($matches) {
                            return '/' . self::URL_PARAM_PREFIX_PLACEHOLDER . ucfirst($matches[1]);
                        },
                        $routePath
                    );
                    $bulkServices[WebapiConverter::KEY_ROUTES][$routePath][$httpMethod] = $httpMethodConfig;
                }
            }
        }
        $bulkServices[WebapiConverter::KEY_SERVICES] = $webapiServices[WebapiConverter::KEY_SERVICES];

        return $bulkServices;
    }
}
