<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\UrlRewrite\Model\MergeDataProviderFactory;

/**
 * Class CompositeUrlFinder
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
        foreach ($this->getChildren() as $child) {
            $urlFinder = $this->objectManager->get($child['class']);
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
        foreach ($this->getChildren() as $child) {
            $urlFinder = $this->objectManager->get($child['class']);
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
        uasort(
            $this->children,
            function ($first, $second) {
                return (int)$first['sortOrder'] <=> (int)$second['sortOrder'];
            }
        );
        return $this->children;
    }
}
