<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config\Provider;

use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollector;
use Magento\Framework\View\Element\UiComponent\Config\FileCollector\AggregatedFileCollectorFactory;

/**
 * Class Template
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $aggregatedFileCollector;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface
     * @since 2.0.0
     */
    protected $domMerger;

    /**
     * @var \Magento\Framework\Config\CacheInterface
     * @since 2.0.0
     */
    protected $cache;

    /**
     * Factory for UI config reader
     *
     * @var \Magento\Framework\View\Element\UiComponent\Config\ReaderFactory
     * @since 2.0.0
     */
    protected $readerFactory;

    /**
     * @var AggregatedFileCollectorFactory
     * @since 2.0.0
     */
    protected $aggregatedFileCollectorFactory;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $cachedTemplates = [];

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param AggregatedFileCollector $aggregatedFileCollector
     * @param \Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface $domMerger
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\View\Element\UiComponent\Config\ReaderFactory $readerFactory
     * @param AggregatedFileCollectorFactory $aggregatedFileCollectorFactory
     * @since 2.0.0
     */
    public function __construct(
        AggregatedFileCollector $aggregatedFileCollector,
        \Magento\Framework\View\Element\UiComponent\Config\DomMergerInterface $domMerger,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\View\Element\UiComponent\Config\ReaderFactory $readerFactory,
        AggregatedFileCollectorFactory $aggregatedFileCollectorFactory
    ) {
        $this->aggregatedFileCollector = $aggregatedFileCollector;
        $this->domMerger = $domMerger;
        $this->cache = $cache;
        $this->readerFactory = $readerFactory;
        $this->aggregatedFileCollectorFactory = $aggregatedFileCollectorFactory;

        $cachedTemplates = $this->cache->load(static::CACHE_ID);
        $this->cachedTemplates = $cachedTemplates === false ? [] : $this->getSerializer()->unserialize(
            $cachedTemplates
        );
    }

    /**
     * Get template content
     *
     * @param string $template
     * @return string
     * @throws \Exception
     * @since 2.0.0
     */
    public function getTemplate($template)
    {
        $hash = sprintf('%x', crc32($template));
        if (isset($this->cachedTemplates[$hash])) {
            return $this->cachedTemplates[$hash];
        }
        $this->domMerger->unsetDom();
        $this->cachedTemplates[$hash] = $this->readerFactory->create(
            [
                'fileCollector' => $this->aggregatedFileCollectorFactory->create(['searchPattern' => $template]),
                'domMerger' => $this->domMerger
            ]
        )->getContent();
        $this->cache->save($this->getSerializer()->serialize($this->cachedTemplates), static::CACHE_ID);

        return $this->cachedTemplates[$hash];
    }

    /**
     * Get serializer
     *
     * @return \Magento\Framework\Serialize\SerializerInterface
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }
        return $this->serializer;
    }
}
