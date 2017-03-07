<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\DataObject;

/**
 * Class process source, cache them and retrieve value by path
 */
class System implements ConfigTypeInterface
{
    const CACHE_TAG = 'config_scopes';

    const CONFIG_TYPE = 'system';

    /**
     * @var \Magento\Framework\App\Config\ConfigSourceInterface
     */
    private $source;

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * @var \Magento\Framework\App\Config\Spi\PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @var \Magento\Framework\App\Config\Spi\PreProcessorInterface
     */
    private $preProcessor;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cachingNestedLevel;

    /**
     * @var \Magento\Store\Model\Config\Processor\Fallback
     */
    private $fallback;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Config\ConfigSourceInterface $source
     * @param \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
     * @param \Magento\Store\Model\Config\Processor\Fallback $fallback
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     */
    public function __construct(
        \Magento\Framework\App\Config\ConfigSourceInterface $source,
        \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor,
        \Magento\Store\Model\Config\Processor\Fallback $fallback,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor,
        $cachingNestedLevel = 1
    ) {
        $this->source = $source;
        $this->postProcessor = $postProcessor;
        $this->preProcessor = $preProcessor;
        $this->cache = $cache;
        $this->cachingNestedLevel = $cachingNestedLevel;
        $this->fallback = $fallback;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function get($path = '')
    {
        if ($path === null) {
            $path = '';
        }
        if (!$this->data) {
            $data = $this->cache->load(self::CONFIG_TYPE);
            if (!$data) {
                $data = $this->preProcessor->process($this->source->get());
                $this->data = new DataObject($data);
                $data = $this->fallback->process($data);
                $this->data = new DataObject($data);
                //Placeholder processing need system config - so we need to save intermediate result
                $data = $this->postProcessor->process($data);
                $this->data = new DataObject($data);
                $this->cache->save(
                    $this->serializer->serialize($this->data->getData()),
                    self::CONFIG_TYPE,
                    [self::CACHE_TAG]
                );
            } else {
                $this->data = new DataObject($this->serializer->unserialize($data));
            }
        }

        return $this->data->getData($path);
    }

    /**
     * Clean cache and global variables cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }
}
