<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Type;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;

/**
 * Class process source, cache them and retrieve value by path
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class System implements ConfigTypeInterface
{
    const CACHE_TAG = 'config_scopes';

    const CONFIG_TYPE = 'system';

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * @var DataObject[]
     */
    private $data;

    /**
     * @var PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @var PreProcessorInterface
     */
    private $preProcessor;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cachingNestedLevel;

    /**
     * @var Fallback
     */
    private $fallback;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * System constructor.
     * @param ConfigSourceInterface $source
     * @param PostProcessorInterface $postProcessor
     * @param Fallback $fallback
     * @param FrontendInterface $cache
     * @param PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigSourceInterface $source,
        PostProcessorInterface $postProcessor,
        Fallback $fallback,
        FrontendInterface $cache,
        SerializerInterface $serializer,
        PreProcessorInterface $preProcessor,
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
