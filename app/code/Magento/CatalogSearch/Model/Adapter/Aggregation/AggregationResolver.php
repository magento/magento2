<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;

/**
 * Aggregation resolver.
 */
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
     * @var RequestCheckerInterface
     */
    private $requestChecker;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * AggregationResolver constructor
     *
     * @param AttributeSetFinderInterface $attributeSetFinder
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param AttributeCollection $attributeCollection [optional]
     * @param RequestCheckerInterface|null $aggregationChecker
     */
    public function __construct(
        AttributeSetFinderInterface $attributeSetFinder,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        ?AttributeCollection $attributeCollection = null,
        ?RequestCheckerInterface $aggregationChecker = null
    ) {
        $this->attributeSetFinder = $attributeSetFinder;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->attributeCollection = $attributeCollection
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeCollection::class);
        $this->requestChecker = $aggregationChecker ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(RequestCheckerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(RequestInterface $request, array $documentIds)
    {
        if (!$this->requestChecker->isApplicable($request)) {
            return [];
        }

        $data = $this->config->get($request->getName());

        $bucketKeys = isset($data['aggregations']) ? array_keys($data['aggregations']) : [];
        $attributeCodes = $this->getApplicableAttributeCodes($documentIds);

        $resolvedAggregation = array_filter(
            $request->getAggregation(),
            function ($bucket) use ($attributeCodes, $bucketKeys) {
                /** @var BucketInterface $bucket */
                return in_array($bucket->getField(), $attributeCodes, true) ||
                    in_array($bucket->getName(), $bucketKeys, true);
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

        $this->attributeCollection->setAttributeSetFilter($attributeSetIds);
        $this->attributeCollection->setEntityTypeFilter(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
        );
        $this->attributeCollection->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('attribute_code');

        return $this->attributeCollection->getConnection()->fetchCol($this->attributeCollection->getSelect());
    }
}
