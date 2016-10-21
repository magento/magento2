<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\App\Config\Type;

use Magento\Framework\App\Cache\Type\Translate;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Cache\FrontendInterface;

/**
 * Class which hold all translation sources and merge them
 *
 * @package Magento\Translation\App\Config\Type
 */
class Translation implements ConfigTypeInterface
{
    const CONFIG_TYPE = "i18n";

    /**
     * @var DataObject[]
     */
    private $data = [];

    /**
     * @var ConfigSourceInterface
     */
    private $source;

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var int
     */
    private $cachingNestedLevel;

    /**
     * Translation constructor.
     * @param ConfigSourceInterface $source
     * @param FrontendInterface $cache
     * @param int $cachingNestedLevel
     */
    public function __construct(
        ConfigSourceInterface $source,
        FrontendInterface $cache,
        $cachingNestedLevel = 1
    ) {
        $this->source = $source;
        $this->cache = $cache;
        $this->cachingNestedLevel = $cachingNestedLevel;
    }

    /**
     * @inheritDoc
     */
    public function get($path = '')
    {
        $cachePath = $this->getCachePath($path);
        if (!isset($this->data[$cachePath])) {
            $data = $this->cache->load(self::CONFIG_TYPE . '/' . $cachePath);
            if (!$data) {
                $this->data[$cachePath] = new DataObject([$cachePath => $this->source->get($cachePath)]);
                $this->cache->save(
                    serialize($this->data),
                    self::CONFIG_TYPE . '/' . $cachePath,
                    [Translate::TYPE_IDENTIFIER]
                );
            } else {
                $this->data = unserialize($data);
            }
        }
        return $this->data[$cachePath]->getData($path);
    }

    /**
     * Build cache path
     *
     * @param string $path
     * @return string
     */
    private function getCachePath($path)
    {
        return implode('/', array_slice(explode('/', $path), 0, $this->cachingNestedLevel));
    }

    /**
     * Clean cache
     *
     * @return void
     */
    public function clean()
    {
        $this->data = null;
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [Translate::TYPE_IDENTIFIER]);
    }
}
