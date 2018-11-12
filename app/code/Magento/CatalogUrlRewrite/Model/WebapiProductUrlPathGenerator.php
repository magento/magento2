<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class for creating product url through web-api.
 */
class WebapiProductUrlPathGenerator extends ProductUrlPathGenerator
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($storeManager, $scopeConfig, $categoryUrlPathGenerator, $productRepository);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    protected function prepareProductUrlKey(\Magento\Catalog\Model\Product $product)
    {
        $urlKey = $product->getUrlKey();
        if ($urlKey === '' || $urlKey === null) {
            $urlKey = $this->prepareUrlKey($product->formatUrlKey($product->getName()));
        }
        return $product->formatUrlKey($urlKey);
    }

    /**
     * Crete url key if it does not exist yet.
     *
     * @param string $urlKey
     * @return string
     */
    private function prepareUrlKey(string $urlKey) : string
    {
        /** @var ProductCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('url_key', ['like' => $urlKey]);
        if ($collection->getSize() !== 0) {
            $urlKey = $urlKey . '-1';
            $urlKey = $this->prepareUrlKey($urlKey);
        }

        return $urlKey;
    }
}
