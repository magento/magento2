<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;

/**
 * Looks up url rewrites across all configured storage finders, sorted by priority
 */
class CompositeUrlFinder implements UrlFinderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $children = [];

    /**
     * @var MergeDataProviderFactory
     */
    private $mergeDataProviderFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var bool
     */
    private $childrenSorted = false;

    /**
     * @var UrlFinderInterface[]
     */
    private $resolvedChildren = [];

    /**
     * @param array $children
     * @param ObjectManagerInterface $objectManager
     * @param MergeDataProviderFactory $mergeDataProviderFactory
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        array $children,
        ObjectManagerInterface $objectManager,
        MergeDataProviderFactory $mergeDataProviderFactory,
        ScopeConfigInterface $config
    ) {
        $this->children = $children;
        $this->objectManager = $objectManager;
        $this->mergeDataProviderFactory = $mergeDataProviderFactory;
        $this->config = $config;
    }

    /**
     * Check config value of generate_category_product_rewrites
     *
     * @return bool
     */
    private function isCategoryRewritesEnabled(): bool
    {
        return (bool)$this->config->getValue('catalog/seo/generate_category_product_rewrites');
    }

    /**
     * @inheritdoc
     */
    public function findAllByData(array $data)
    {
        $isDynamicRewrites = !$this->isCategoryRewritesEnabled();

        $mergeDataProvider = $this->mergeDataProviderFactory->create();
        foreach ($this->getChildren() as $key => $child) {
            $urlFinder = $this->getChildFinder($key, $child);
            $rewrites = $urlFinder->findAllByData($data);
            if (!$isDynamicRewrites) {
                return $rewrites;
            }
            $mergeDataProvider->merge($rewrites);
        }
        return $mergeDataProvider->getData();
    }

    /**
     * @inheritdoc
     */
    public function findOneByData(array $data)
    {
        foreach ($this->getChildren() as $key => $child) {
            $urlFinder = $this->getChildFinder($key, $child);
            $rewrite = $urlFinder->findOneByData($data);
            if (!empty($rewrite)) {
                return $rewrite;
            }
        }
        return null;
    }

    /**
     * Get children in sorted order
     *
     * @return array
     */
    private function getChildren(): array
    {
        if (!$this->childrenSorted) {
            uasort(
                $this->children,
                function ($first, $second) {
                    return (int)$first['sortOrder'] <=> (int)$second['sortOrder'];
                }
            );
            $this->childrenSorted = true;
        }
        return $this->children;
    }

    /**
     * Resolve and cache the finder instance for a child configuration entry
     *
     * @param int|string $key
     * @param array $child
     * @return UrlFinderInterface
     */
    private function getChildFinder($key, array $child): UrlFinderInterface
    {
        if (!isset($this->resolvedChildren[$key])) {
            $this->resolvedChildren[$key] = $this->objectManager->get($child['class']);
        }
        return $this->resolvedChildren[$key];
    }
}
