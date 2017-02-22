<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\Provider;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\View\Element\UiComponent\Config\ReaderFactory;
use Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory;

/**
 * Class Template
 */
class Template
{
    /**
     * Components node name in config
     */
    const TEMPLATE_KEY = 'template';

    /**
     * ID in the storage cache
     */
    const CACHE_ID = 'ui_component_templates';

    /**
     * @var AggregatedFileCollector
     */
    protected $aggregatedFileCollector;

    /**
     * @var DomMergerInterface
     */
    protected $domMerger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Factory for UI config reader
     *
     * @var ReaderFactory
     */
    protected $readerFactory;

    /**
     * @var AggregatedFileCollectorFactory
     */
    protected $aggregatedFileCollectorFactory;

    /**
     * @var array
     */
    protected $cachedTemplates = [];

    /**
     * Constructor
     *
     * @param AggregatedFileCollector $aggregatedFileCollector
     * @param DomMergerInterface $domMerger
     * @param CacheInterface $cache
     * @param ReaderFactory $readerFactory
     * @param AggregatedFileCollectorFactory $aggregatedFileCollectorFactory
     */
    public function __construct(
        AggregatedFileCollector $aggregatedFileCollector,
        DomMergerInterface $domMerger,
        CacheInterface $cache,
        ReaderFactory $readerFactory,
        AggregatedFileCollectorFactory $aggregatedFileCollectorFactory
    ) {
        $this->aggregatedFileCollector = $aggregatedFileCollector;
        $this->domMerger = $domMerger;
        $this->cache = $cache;
        $this->readerFactory = $readerFactory;
        $this->aggregatedFileCollectorFactory = $aggregatedFileCollectorFactory;

        $cachedTemplates = $this->cache->load(static::CACHE_ID);
        $this->cachedTemplates = $cachedTemplates === false ? [] : unserialize($cachedTemplates);
    }

    /**
     * Get template content
     *
     * @param string $template
     * @return string
     * @throws \Exception
     */
    public function getTemplate($template)
    {
        $hash = sprintf('%x', crc32($template));
        if (isset($this->cachedTemplates[$hash])) {
            return $this->cachedTemplates[$hash];
        }

        $this->cachedTemplates[$hash] = $this->readerFactory->create(
            [
                'fileCollector' => $this->aggregatedFileCollectorFactory->create(['searchPattern' => $template]),
                'domMerger' => $this->domMerger
            ]
        )->getContent();
        $this->cache->save(serialize($this->cachedTemplates), static::CACHE_ID);

        return $this->cachedTemplates[$hash];
    }
}
