<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer\Search as CatalogLayerSearch;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\Request\Config as RequestConfig;
use Magento\Search\Model\Search;

/**
 * Quick search products by query.
 */
class QuickSearchByQuery
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Flush search instances cache and find products by search query.
     *
     * @param string $query
     * @return Collection
     */
    public function execute(string $query): Collection
    {
        $this->removeInstancesCache();
        $productCollection = $this->getCatalogLayerSearch()->getProductCollection();
        $productCollection->addSearchFilter($query);
        $productCollection->setOrder('relevance', 'desc');

        return $productCollection;
    }

    /**
     * Retrieve empty catalog layer search instance.
     *
     * @return CatalogLayerSearch
     */
    private function getCatalogLayerSearch(): CatalogLayerSearch
    {
        return $this->objectManager->create(CatalogLayerSearch::class);
    }

    /**
     * Remove instances cache which related to search.
     *
     * @return void
     */
    private function removeInstancesCache(): void
    {
        $this->objectManager->removeSharedInstance(RequestConfig::class);
        $this->objectManager->removeSharedInstance(Builder::class);
        $this->objectManager->removeSharedInstance(Search::class);
    }
}
