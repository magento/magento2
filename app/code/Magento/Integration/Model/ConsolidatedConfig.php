<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Integration\Model\Cache\TypeConsolidated;

/**
 * ConsolidatedConfig to deliver information for config-based integrations that use integration.xml
 */
class ConsolidatedConfig
{
    const CACHE_ID = 'integration-consolidated';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $configCacheType;

    /**
     * @var \Magento\Integration\Model\Config\Consolidated\Reader
     */
    protected $configReader;

    /**
     * Array of integrations
     *
     * @var array
     */
    protected $integrations;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param Cache\TypeConsolidated $configCacheType
     * @param Config\Consolidated\Reader $configReader
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Cache\TypeConsolidated $configCacheType,
        Config\Consolidated\Reader $configReader,
        SerializerInterface $serializer = null
    ) {
        $this->configCacheType = $configCacheType;
        $this->configReader = $configReader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Return integrations loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getIntegrations()
    {
        if (null === $this->integrations) {
            $integrations = $this->configCacheType->load(self::CACHE_ID);
            if ($integrations && is_string($integrations)) {
                $this->integrations = $this->serializer->unserialize($integrations);
            } else {
                $this->integrations = $this->configReader->read();
                $this->configCacheType->save(
                    $this->serializer->serialize($this->integrations),
                    self::CACHE_ID,
                    [TypeConsolidated::CACHE_TAG]
                );
            }
        }
        return $this->integrations;
    }
}
