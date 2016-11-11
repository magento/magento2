<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;

/**
 * Merge and hold scopes data from different sources
 *
 * @package Magento\Store\App\Config\Type
 */
class Scopes implements ConfigTypeInterface
{
    const CONFIG_TYPE = 'scopes';

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cachingNestedLevel;

    /**
     * @var Serialize
     */
    private $serializer;

    /**
     * System constructor.
     * @param ConfigSourceInterface $source
     * @param FrontendInterface $cache
     * @param int $cachingNestedLevel
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigSourceInterface $source,
        FrontendInterface $cache,
        Serialize $serializer,
        $cachingNestedLevel = 1
    ) {
        $this->source = $source;
        $this->cache = $cache;
        $this->cachingNestedLevel = $cachingNestedLevel;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        if (!$this->data) {
            /** @var DataObject $data */
            $data = $this->cache->load(self::CONFIG_TYPE);
            if (!$data) {
                $this->data = new DataObject($this->source->get());
                $this->cache->save(
                    $this->serializer->serialize($this->data->getData()),
                    self::CONFIG_TYPE,
                    [Group::CACHE_TAG, Store::CACHE_TAG, Website::CACHE_TAG]
                );
            } else {
                $this->data = new DataObject($this->serializer->unserialize($data));
            }
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
        $this->cache->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            [Group::CACHE_TAG, Store::CACHE_TAG, Website::CACHE_TAG]
        );
    }
}
