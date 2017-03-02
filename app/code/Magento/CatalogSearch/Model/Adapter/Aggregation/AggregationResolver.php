<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation;

use Magento\Catalog\Api\AttributeSetFinderInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Config;
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
     * @var Config
     */
    private $config;
    
    /**
     * AggregationResolver constructor
     *
     * @param AttributeSetFinderInterface $attributeSetFinder
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     */
    public function __construct(
        AttributeSetFinderInterface $attributeSetFinder,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config
    ) {
        $this->attributeSetFinder = $attributeSetFinder;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(RequestInterface $request, array $documentIds)
    {
        $data = $this->config->get($request->getName());

        $bucketKeys = isset($data['aggregations']) ? array_keys($data['aggregations']) : [];
        $attributeCodes = $this->getApplicableAttributeCodes($documentIds);

        $resolvedAggregation = array_filter(
            $request->getAggregation(),
            function ($bucket) use ($attributeCodes, $bucketKeys) {
                /** @var BucketInterface $bucket */
                return in_array($bucket->getField(), $attributeCodes) || in_array($bucket->getName(), $bucketKeys);
            }
        );
        return array_values($resolvedAggregation);
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
