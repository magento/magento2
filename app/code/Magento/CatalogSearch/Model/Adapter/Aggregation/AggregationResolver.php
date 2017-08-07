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
 * Class \Magento\CatalogSearch\Model\Adapter\Aggregation\AggregationResolver
 *
 * @since 2.1.0
 */
class AggregationResolver implements AggregationResolverInterface
{
    /**
     * @var AttributeSetFinderInterface
     * @since 2.1.0
     */
    private $attributeSetFinder;

    /**
     * @var ProductAttributeRepositoryInterface
     * @since 2.1.0
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    private $searchCriteriaBuilder;

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @var RequestCheckerInterface
     * @since 2.2.0
     */
    private $requestChecker;

    /**
     * @var AttributeCollection
     * @since 2.2.0
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
     * @since 2.1.0
     */
    public function __construct(
        AttributeSetFinderInterface $attributeSetFinder,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        AttributeCollection $attributeCollection = null,
        RequestCheckerInterface $aggregationChecker = null
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
     * {@inheritdoc}
     * @since 2.1.0
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
     * @since 2.1.0
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
