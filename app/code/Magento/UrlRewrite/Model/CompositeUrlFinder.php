<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\UrlRewrite\Model\MergeDataProviderFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

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
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @param array $children
     * @param ObjectManagerInterface $objectManager
     * @param MergeDataProviderFactory $mergeDataProviderFactory
     * @param ScopeConfigInterface $config
     * @param StoreResolverInterface $storeResolver
     */
    public function __construct(
        array $children,
        ObjectManagerInterface $objectManager,
        MergeDataProviderFactory $mergeDataProviderFactory,
        ScopeConfigInterface $config,
        StoreResolverInterface $storeResolver
    ) {
        $this->children = $children;
        $this->objectManager = $objectManager;
        $this->mergeDataProviderFactory = $mergeDataProviderFactory;
        $this->config = $config;
        $this->storeResolver = $storeResolver;
    }

    /**
     * Check config value of generate_rewrites_on_save
     *
     * @param int $storeId
     * @return bool
     */
    private function isCategoryRewritesEnabled($storeId)
    {
        return (bool)$this->config->getValue(
            'catalog/seo/generate_rewrites_on_save',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritdoc
     */
    public function findAllByData(array $data)
    {
        $store = isset($data[UrlRewrite::STORE_ID])
            ? $data[UrlRewrite::STORE_ID]
            : $this->storeResolver->getCurrentStoreId();
        $isDynamicRewrites = !$this->isCategoryRewritesEnabled((int)$store);

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
    private function getChildren()
    {
        uasort($this->children, function ($first, $second) {
            return (int)$first['sortOrder'] <=> (int)$second['sortOrder'];
        });
        return $this->children;
    }
}
