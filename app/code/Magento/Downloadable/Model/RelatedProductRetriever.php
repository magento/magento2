<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Related parent product retriever.
 */
class RelatedProductRetriever
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MetadataPool $metadataPool
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get related product.
     *
     * @param int $productId
     * @return ProductInterface|null
     */
    public function getProduct(int $productId): ?ProductInterface
    {
        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter($productMetadata->getLinkField(), $productId)
            ->create();
        $items = $this->productRepository->getList($searchCriteria)
            ->getItems();
        $product = $items ? array_shift($items) : null;

        return $product;
    }
}
