<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\Plugin\Search\Request;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

/**
 * Add search request configuration to config for give ability filter and search products during GraphQL request
 * Add 2 request name with and without aggregation correspondingly:
 * - graphql_product_search_with_aggregation
 * - graphql_product_search
 *
 * @see Magento/CatalogGraphQl/etc/search_request.xml
 */
class ConfigReader
{
    /**
     * @var string
     */
    private $requestNameWithAggregation = 'graphql_product_search_with_aggregation';

    /**
     * @var string
     */
    private $requestName = 'graphql_product_search';

    /**
     * @var GeneratorResolver
     */
    private $generatorResolver;

    /**
     * @var CollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @var EngineResolverInterface
     */
    private $searchEngineResolver;

    /** Bucket name suffix */
    private const BUCKET_SUFFIX = '_bucket';

    /**
     * @param GeneratorResolver $generatorResolver
     * @param CollectionFactory $productAttributeCollectionFactory
     * @param EngineResolverInterface $searchEngineResolver
     */
    public function __construct(
        GeneratorResolver $generatorResolver,
        CollectionFactory $productAttributeCollectionFactory,
        EngineResolverInterface $searchEngineResolver
    ) {
        $this->generatorResolver = $generatorResolver;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->searchEngineResolver = $searchEngineResolver;
    }

    /**
     * Merge reader's value with generated
     *
     * @param \Magento\Framework\Config\ReaderInterface $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRead(
        \Magento\Framework\Config\ReaderInterface $subject,
        array $result
    ) {
        $searchRequestNameWithAggregation = $this->generateRequest();
        $searchRequest = $searchRequestNameWithAggregation;
        $searchRequest['queries'][$this->requestName] = $searchRequest['queries'][$this->requestNameWithAggregation];
        unset($searchRequest['queries'][$this->requestNameWithAggregation], $searchRequest['aggregations']);

        return array_merge_recursive(
            $result,
            [
                $this->requestNameWithAggregation => $searchRequestNameWithAggregation,
                $this->requestName => $searchRequest,
            ]
        );
    }

    /**
     * Retrieve searchable attributes
     *
     * @return Attribute[]
     */
    private function getSearchableAttributes(): array
    {
        $attributes = [];
        /** @var Collection $productAttributes */
        $productAttributes = $this->productAttributeCollectionFactory->create();
        $productAttributes->addFieldToFilter(
            ['is_searchable', 'is_visible_in_advanced_search', 'is_filterable', 'is_filterable_in_search'],
            [1, 1, [1, 2], 1]
        );

        /** @var Attribute $attribute */
        foreach ($productAttributes->getItems() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        return $attributes;
    }

    /**
     * Generate search request for search products via GraphQL
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateRequest()
    {
        $request = [];
        foreach ($this->getSearchableAttributes() as $attribute) {
            if (\in_array($attribute->getAttributeCode(), ['price', 'visibility', 'category_ids'])) {
                //some fields have special semantics
                continue;
            }
            $queryName = $attribute->getAttributeCode() . '_query';
            $filterName = $attribute->getAttributeCode() . RequestGenerator::FILTER_SUFFIX;
            $request['queries'][$this->requestNameWithAggregation]['queryReference'][] = [
                'clause' => 'must',
                'ref' => $queryName,
            ];

            switch ($attribute->getBackendType()) {
                case 'static':
                case 'text':
                case 'varchar':
                    if ($attribute->getFrontendInput() === 'multiselect') {
                        $request['queries'][$queryName] = $this->generateFilterQuery($queryName, $filterName);
                        $request['filters'][$filterName] = $this->generateTermFilter($filterName, $attribute);
                    } elseif ($attribute->getAttributeCode() === 'sku') {
                        $request['queries'][$queryName] = $this->generateFilterQuery($queryName, $filterName);
                        $request['filters'][$filterName] = $this->generateSkuTermFilter($filterName, $attribute);
                    } else {
                        $request['queries'][$queryName] = $this->generateMatchQuery($queryName, $attribute);
                    }
                    break;
                case 'decimal':
                case 'datetime':
                case 'date':
                    $request['queries'][$queryName] = $this->generateFilterQuery($queryName, $filterName);
                    $request['filters'][$filterName] = $this->generateRangeFilter($filterName, $attribute);
                    break;
                default:
                    $request['queries'][$queryName] = $this->generateFilterQuery($queryName, $filterName);
                    $request['filters'][$filterName] = $this->generateTermFilter($filterName, $attribute);
            }
            $generator = $this->generatorResolver->getGeneratorForType($attribute->getBackendType());

            if ($attribute->getData(EavAttributeInterface::IS_FILTERABLE)) {
                $bucketName = $attribute->getAttributeCode() . self::BUCKET_SUFFIX;
                $request['aggregations'][$bucketName] = $generator->getAggregationData($attribute, $bucketName);
            }

            $this->addSearchAttributeToFullTextSearch($attribute, $request);
        }

        return $request;
    }

    /**
     * Add attribute with specified boost to "search" query used in full text search
     *
     * @param Attribute $attribute
     * @param array $request
     * @return void
     */
    private function addSearchAttributeToFullTextSearch(Attribute $attribute, &$request): void
    {
        // Match search by custom price attribute isn't supported
        if ($attribute->getFrontendInput() !== 'price') {
            $request['queries']['search']['match'][] = [
                'field' => $attribute->getAttributeCode(),
                'boost' => $attribute->getSearchWeight() ?: 1,
            ];
        }
    }

    /**
     * Return array representation of range filter
     *
     * @param string $filterName
     * @param Attribute $attribute
     * @return array
     */
    private function generateRangeFilter(string $filterName, Attribute $attribute)
    {
        return [
            'field' => $attribute->getAttributeCode(),
            'name' => $filterName,
            'type' => FilterInterface::TYPE_RANGE,
            'from' => '$' . $attribute->getAttributeCode() . '.from$',
            'to' => '$' . $attribute->getAttributeCode() . '.to$',
        ];
    }

    /**
     * Return array representation of term filter
     *
     * @param string $filterName
     * @param Attribute $attribute
     * @return array
     */
    private function generateTermFilter(string $filterName, Attribute $attribute)
    {
        return [
            'type' => FilterInterface::TYPE_TERM,
            'name' => $filterName,
            'field' => $attribute->getAttributeCode(),
            'value' => '$' . $attribute->getAttributeCode() . '$',
        ];
    }

    /**
     * Generate term filter for sku field
     *
     * Sku needs to be treated specially to allow for exact match
     *
     * @param string $filterName
     * @param Attribute $attribute
     * @return array
     */
    private function generateSkuTermFilter(string $filterName, Attribute $attribute)
    {
        $field = $this->isElasticSearch() ? 'sku.filter_sku' : 'sku';

        return [
            'type' => FilterInterface::TYPE_TERM,
            'name' => $filterName,
            'field' => $field,
            'value' => '$' . $attribute->getAttributeCode() . '$',
        ];
    }

    /**
     * Return array representation of query based on filter
     *
     * @param string $queryName
     * @param string $filterName
     * @return array
     */
    private function generateFilterQuery(string $queryName, string $filterName)
    {
        return [
            'name' => $queryName,
            'type' => QueryInterface::TYPE_FILTER,
            'filterReference' => [
                [
                    'ref' => $filterName,
                ],
            ],
        ];
    }

    /**
     * Return array representation of match query
     *
     * @param string $queryName
     * @param Attribute $attribute
     * @return array
     */
    private function generateMatchQuery(string $queryName, Attribute $attribute)
    {
        return [
            'name' => $queryName,
            'type' => 'matchQuery',
            'value' => '$' . $attribute->getAttributeCode() . '$',
            'match' => [
                [
                    'field' => $attribute->getAttributeCode(),
                    'boost' => $attribute->getSearchWeight() ?: 1,
                ],
            ],
        ];
    }

    /**
     * Check if the current search engine is elasticsearch
     *
     * @return bool
     */
    private function isElasticSearch()
    {
        $searchEngine = $this->searchEngineResolver->getCurrentSearchEngine();
        if (strpos($searchEngine, 'elasticsearch') === 0) {
            return true;
        }
        return false;
    }
}
