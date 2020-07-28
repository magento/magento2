<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * CSP aware block cache.
 */
class BlockCache implements CacheInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var DynamicCollector
     */
    private $dynamicCollector;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param CacheInterface $cache
     * @param DynamicCollector $dynamicCollector
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CacheInterface $cache,
        DynamicCollector $dynamicCollector,
        SerializerInterface $serializer
    ) {
        $this->cache = $cache;
        $this->dynamicCollector = $dynamicCollector;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function getFrontend()
    {
        return $this->cache->getFrontend();
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        /** @var array|null $data */
        $data = null;
        $loaded = $this->cache->load($identifier);
        try {
            $data = $this->serializer->unserialize($loaded);
            if (!is_array($data) || !array_key_exists('policies', $data) || !array_key_exists('html', $data)) {
                $data = null;
            }
        } catch (\Throwable $exception) {
            //Most likely block HTML was cached without policy data.
            $data = null;
        }
        if ($data) {
            foreach ($data['policies'] as $policyData) {
                $this->dynamicCollector->add(
                    new FetchPolicy(
                        $policyData['id'],
                        false,
                        $policyData['hosts'],
                        [],
                        false,
                        false,
                        false,
                        [],
                        $policyData['hashes']
                    )
                );
            }
            $loaded = $data['html'];
        }

        return $loaded;
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        $collected = $this->dynamicCollector->collect();
        if ($collected) {
            $policiesData = [];
            foreach ($collected as $policy) {
                if ($policy instanceof FetchPolicy) {
                    $policiesData[] = [
                        'id' => $policy->getId(),
                        'hosts' => $policy->getHostSources(),
                        'hashes' => $policy->getHashes()
                    ];
                }
            }
            $data = $this->serializer->serialize(['policies' => $policiesData, 'html' => $data]);
        }

        return $this->cache->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        return $this->cache->remove($identifier);
    }

    /**
     * @inheritDoc
     */
    public function clean($tags = [])
    {
        return $this->cache->clean($tags);
    }
}
