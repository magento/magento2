<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation;

use Magento\Catalog\Api\AttributeSetFinderInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;

class AggregationResolver implements AggregationResolverInterface
{
    /**
     * @var AttributeSetFinderInterface
     */
    private $attributeSetFinder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    
    /**
     * AggregationResolver constructor
     *
     * @param AttributeSetFinderInterface $attributeSetFinder
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeSetFinderInterface $attributeSetFinder,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeSetFinder = $attributeSetFinder;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(RequestInterface $request, array $documentIds)
    {
        $attributeCodes = $this->getApplicableAttributeCodes($documentIds);

        $resolvedAggregation = array_filter($request->getAggregation(), function ($bucket) use ($attributeCodes) {
            /** @var BucketInterface $bucket */
            return in_array($bucket->getField(), $attributeCodes);
        });
        return $resolvedAggregation;
    }

    /**
     * Get applicable attributes
     *
     * @param array $documentIds
     * @return array
     */
    private function getApplicableAttributeCodes(array $documentIds)
    {
        $attributeSetIds = $this->attributeSetFinder->findAttributeSetIdsByProductIds($documentIds);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_set_id', $attributeSetIds, 'in')
            ->create();
        $result = $this->productAttributeRepository->getList($searchCriteria);

        $attributeCodes = [];
        foreach ($result->getItems() as $attribute) {
            $attributeCodes[] = $attribute->getAttributeCode();
        }
        return $attributeCodes;
    }
}
