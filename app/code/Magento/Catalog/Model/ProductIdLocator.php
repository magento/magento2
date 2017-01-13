<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * Product ID locator provides all product IDs by SKUs.
 * @api
 */
class ProductIdLocator implements \Magento\Catalog\Model\ProductIdLocatorInterface
{
    /**
     * Search Criteria builder.
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Filter builder.
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * Metadata pool.
     *
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * Product repository.
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * IDs by SKU cache.
     *
     * @var array
     */
    private $idsBySku = [];

    /**
     * ProductIdLocatorInterface constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->metadataPool = $metadataPool;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveProductIdsBySkus(array $skus)
    {
        $skus = array_map('trim', $skus);
        $skusInCache = $this->idsBySku ? array_keys($this->idsBySku) : [];
        $neededSkus = array_diff($skus, $skusInCache);

        if (!empty($neededSkus)) {
            $searchCriteria = $this->searchCriteriaBuilder->addFilters(
                [
                    $this->filterBuilder
                        ->setField(\Magento\Catalog\Api\Data\ProductInterface::SKU)
                        ->setConditionType('in')
                        ->setValue($neededSkus)
                        ->create(),
                ]
            );
            $items = $this->productRepository->getList($searchCriteria->create())->getItems();
            $linkField = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();

            foreach ($items as $item) {
                $this->idsBySku[$item->getSku()][$item->getData($linkField)] = $item->getTypeId();
            }
        }

        return array_intersect_key($this->idsBySku, array_flip($skus));
    }
}
