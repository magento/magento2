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
     * @param array $children
     * @param ObjectManagerInterface $objectManager
     * @param MergeDataProviderFactory $mergeDataProviderFactory
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        private array $children,
        private readonly ObjectManagerInterface $objectManager,
        private readonly MergeDataProviderFactory $mergeDataProviderFactory,
        private readonly ScopeConfigInterface $config
    ) {
        $this->children = $children ?? [];
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
